<?php
namespace app\controllers;

use app\models\database\Addresses;
use app\models\database\Users;
use app\models\CreateAccount;
use app\models\CreatePersonal;
use app\models\LoginForm;
use Exception;
use core\controller\Controller;
use core\web\Request;
use core\web\Response;
use core\web\Viewer;
use Override;
use core\web\Session;

use function call_user_func;

class AuthController extends Controller {
    private const TIMER = 3600;
    private const TITLES = [
        "/register/account" => "Register Account - LianMart",
        "/register/personal" => "Register Personal - LianMart",
        "/register/delivery" => "Register Delivery - LianMart",
        "/login" => "Log In - LianMart"
    ];

    #[Override]
    public function index(Request $request): Response {
        if ($request->isGet()) {
            if ($request->path() === "/logout") {
                call_user_func($this->injectedData["logout"]);
                return Response::redirect("/home");
            }
            return Response::create()
                ->content($this->viewContents($request));
        }

        // POST Requests
        $requestPath = $request->path();
        $requestBody = $request->body();
        if (isset($requestBody["form_action"]) && $requestBody["form_action"] === "return_confirmed") {
            $this->removeTimed();
            return Response::redirect("/home");
        }

        if (str_starts_with($requestPath, "/register")) {
            $this->createValidRedirect();
            switch ($requestPath) {
                case "/register/account":
                    return $this->createAccount($request);
                case "/register/personal":
                    return $this->createPersonal($request);
                case "/register/delivery":
                    return $this->createDelivery($request);
                default:
                    throw new Exception("Route Not Found!", 404);
            }
        } else if ($request->path() === "/login") {
            return $this->login($request);
        } else {
            throw new Exception("Route Not Found!", 404);
        }
    }

    private function createValidRedirect(): void {
        $this->getSession()->setFlash("isValidRedirect", true);
    }

    private function getSession(): Session {
        /** @var Session */
        return static::getShared("session");
    }

    private function removeTimed(): void {
        $session = $this->getSession();
        $session->removeTimed("email");
        $session->removeTimed("password");
        $session->removeTimed("confirmPassword");
        $session->removeTimed("firstname");
        $session->removeTimed("lastname");
        $session->removeTimed("birthdate");
        $session->removeTimed("phoneNumber");
    }

    private function viewContents(Request $request): string {
        $model = match ($request->path()) {
            "/register/account" => $this->getAccount(),
            "/register/personal" => $this->getPersonal(),
            default => null
        };
        return Viewer::getContents($request->path(), "auth", ["model" => $model, "title" => self::TITLES[$request->path()]]);
    }

    private function createAccount(Request $request): Response {
        $account = new CreateAccount();
        $account->loadData($request->body());
        if (!$account->validate()) {
            $payload = ["model" => $account, "title" => self::TITLES[$request->path()]];
            return Response::create()
                    ->contentFrom($request->path(), "auth", $payload);
        } else {
            $session = $this->getSession();
            $session->setTimed("email", $account->email, self::TIMER);
            $session->setTimed("password", $account->password, self::TIMER);
            $session->setTimed("confirmPassword", $account->confirmPassword, self::TIMER);
            return Response::redirect("/register/personal");
        }
    }

    private function createPersonal(Request $request): Response {
        $requestBody = $request->body();
        if ($requestBody["action"] === "back") {
            return Response::redirect("/register/account");
        }
        $personal = new CreatePersonal();
        $personal->loadData($requestBody);
        if (!$personal->validate()) {
            $payload = ["model" => $personal, "title" => self::TITLES[$request->path()]];
            return Response::create()
                    ->contentFrom($request->path(), "auth", $payload);
        } else {
            $session = $this->getSession();
            $session->setTimed("firstname", $personal->firstname, self::TIMER);
            $session->setTimed("lastname", $personal->lastname, self::TIMER);
            $session->setTimed("birthdate", $personal->birthdate, self::TIMER);
            $session->setTimed("phoneNumber", $personal->phoneNumber, self::TIMER);
            return Response::redirect("/register/delivery");
        }
    }

    private function createDelivery(Request $request): Response {
        $requestBody = $request->body();
        if ($requestBody["action"] === "back") {
            return Response::redirect("/register/personal");
        }
        $address = new Addresses();
        $address->loadData($requestBody);
        if (!$address->validate()) {
            $payload = ["model" => $address, "title" => self::TITLES[$request->path()]];
            return Response::create()
                    ->contentFrom($request->path(), "auth", $payload);
        } else {
            $session = $this->getSession();
            $user = new Users();
            $user->loadData([
                "firstname" => $session->getTimed("firstname"),
                "lastname" => $session->getTimed("lastname"),
                "email" => $session->getTimed("email"),
                "password" => $session->getTimed("password"),
                "birthdate" => $session->getTimed("birthdate"),
                "phoneNumber" => $session->getTimed("phoneNumber"),
            ]);
            $user->insert();
            call_user_func($this->injectedData["login"], $user);
            $this->removeTimed();

            $address->loadData([
                "userId" => $user->userId
            ]);
            $address->insert();
            return Response::redirect("/home");
        }
    }

    private function login(Request $request): Response {
        $login = new LoginForm();
        $login->loadData($request->body());
        $payload = ["model" => $login, "title" => self::TITLES[$request->path()]];
        if (!$login->validate()) {
            return Response::create()
                    ->contentFrom($request->path(), "auth", $payload);
        }
        $user = $login->attemptLogin();
        if (!$user) {
            return Response::create()
                    ->contentFrom($request->path(), "auth", $payload);
        }
        call_user_func($this->injectedData["login"], $user);
        return Response::redirect("/home");
    }

    private function getAccount(): CreateAccount {
        $session = $this->getSession();
        $account = new CreateAccount();
        $account->loadData([
            "email" => $session->getTimed("email") ?? "",
            "password" => $session->getTimed("password") ?? "",
            "confirmPassword" => $session->getTimed("confirmPassword") ?? ""
        ]);
        return $account;
    }

    private function getPersonal(): CreatePersonal {
        $session = $this->getSession();
        $personal = new CreatePersonal();
        $personal->loadData([
            "firstname" => $session->getTimed("firstname") ?? "",
            "lastname" => $session->getTimed("lastname") ?? "",
            "birthdate" => $session->getTimed("birthdate") ?? "",
            "phoneNumber" => $session->getTimed("phoneNumber") ?? "",
        ]);
        return $personal;
    }
}