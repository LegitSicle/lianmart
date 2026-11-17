<?php
use app\controllers\AdminFinanceController;
use app\controllers\AdminOrderController;
use app\controllers\AdminProductController;
use app\controllers\AdminUserController;
use app\controllers\api\FinanceDataController;
use app\controllers\api\SyncCartController;
use app\controllers\AuthController;
use app\controllers\CartController;
use app\controllers\CatalogController;
use app\controllers\DetailsController;
use app\controllers\ErrorController;
use app\controllers\PageController;
use app\middlewares\AdminMiddleware;
use app\middlewares\DisabledUserMiddleware;
use app\middlewares\LoggedInMiddleware;
use app\middlewares\OnlyUserMiddleware;
use app\middlewares\RedirectMiddleware;
use app\middlewares\ValidProductMiddleware;
use core\Application;
use core\web\Route;

return function (Application $app): void {
    $app->router->addRoute((new Route())
        ->redirect("/", "/home")
    );
    $app->router->addRoute((new Route())
        ->redirect("/register", "/register/account")
    );

    $app->router->addRoute((new Route())
        ->get("/error/403", [ErrorController::class])
    );

    $app->router->addRoute((new Route())
        ->get("/home", [PageController::class, "session" => $app->session, "database" => $app->database])
        ->middlewares(
            [DisabledUserMiddleware::class],
            [OnlyUserMiddleware::class]
        )
    );

    $app->router->addRoute((new Route())
        ->get("/catalog", [CatalogController::class, 
            "sql" => $app->database->createSQL(), 
            "username" => $app->getUsername(), 
            "userId" => $app->getUserId()
        ])
        ->middlewares(
            [DisabledUserMiddleware::class],
            [OnlyUserMiddleware::class]
        )
    );

    $app->router->addRoute((new Route())
        ->get("/delivery", [PageController::class])
        ->middlewares(
            [LoggedInMiddleware::class],
            [DisabledUserMiddleware::class],
            [OnlyUserMiddleware::class]
        )
    );

    $app->router->addRoute((new Route())
        ->get("/cart", [CartController::class, 
            "sql" => $app->database->createSQL(), 
            "username" => $app->getUsername(), 
            "userId" => $app->getUserId()
        ])
        ->middlewares(
            [LoggedInMiddleware::class],
            [DisabledUserMiddleware::class],
            [OnlyUserMiddleware::class]
        )
    );

    $app->router->addRoute((new Route())
        ->get("/product-details", [DetailsController::class,
            "sql" => $app->database->createSQL()
        ])
        ->middlewares(
            [DisabledUserMiddleware::class],
            [OnlyUserMiddleware::class],
            [ValidProductMiddleware::class]
        )
    );

    $app->router->addRoute((new Route())
        ->get("/ordered", [PageController::class])
        ->middlewares(
            [DisabledUserMiddleware::class],
            [OnlyUserMiddleware::class],
            [RedirectMiddleware::class, "/cart"]
        )
    );

    $app->router->addRoute((new Route())
        ->get("/contact", [PageController::class])
        ->middlewares([OnlyUserMiddleware::class])
    );

    $app->router->addRoute((new Route())
        ->get("/profile", [PageController::class])
        ->middlewares(
            [LoggedInMiddleware::class],
            [DisabledUserMiddleware::class],
            [OnlyUserMiddleware::class],
        )
    );

    $app->router->addRoute((new Route())
        ->get("/register/account", [AuthController::class])
        ->middlewares(
            [DisabledUserMiddleware::class],
            [OnlyUserMiddleware::class]
        )
    );

    $app->router->addRoute((new Route())
        ->get("/register/personal", [AuthController::class])
        ->middlewares(
            [DisabledUserMiddleware::class],
            [OnlyUserMiddleware::class],
            [RedirectMiddleware::class, "/register/account"]
        )
    );

    $app->router->addRoute((new Route())
        ->get("/register/delivery", [AuthController::class])
        ->middlewares(
            [DisabledUserMiddleware::class],
            [OnlyUserMiddleware::class],
            [RedirectMiddleware::class, "/register/account"]
        )
    );

    $app->router->addRoute((new Route())
        ->get("/login", [AuthController::class])
        ->middlewares(
            [DisabledUserMiddleware::class],
            [OnlyUserMiddleware::class]
        )
    );

    $app->router->addRoute((new Route())
        ->get("/logout", [AuthController::class, 
            "logout" => $app->logout(...)
        ])
    );

    $app->router->addRoute((new Route())
        ->get("/admin/orders", [AdminOrderController::class,
            "sql" => $app->database->createSQL()
        ])
        ->middlewares(
            [DisabledUserMiddleware::class],
            [AdminMiddleware::class]
        )
    );

    $app->router->addRoute((new Route())
        ->get("/admin/products", [AdminProductController::class, 
            "sql" => $app->database->createSQL()
        ])
        ->middlewares(
            [DisabledUserMiddleware::class],
            [AdminMiddleware::class]
        )
    );

    $app->router->addRoute((new Route())
        ->get("/admin/users", [AdminUserController::class,
            "sql" => $app->database->createSQL()
        ])
        ->middlewares(
            [DisabledUserMiddleware::class],
            [AdminMiddleware::class]
        )
    );

    $app->router->addRoute((new Route())
        ->get("/admin/finances", [AdminFinanceController::class,
            "sql" => $app->database->createSQL()
        ])
        ->middlewares(
            [DisabledUserMiddleware::class],
            [AdminMiddleware::class]
        )
    );

    $app->router->addRoute((new Route())
        ->post("/admin/products", [AdminProductController::class, 
            "sql" => $app->database->createSQL()
        ])
        ->middlewares(
            [DisabledUserMiddleware::class],
            [AdminMiddleware::class]
        )
    );

    $app->router->addRoute((new Route())
        ->post("/login", [AuthController::class, 
            "login" => $app->login(...)
        ])
    );

    $app->router->addRoute((new Route())
        ->post("/product-details", [CatalogController::class, 
            "sql" => $app->database->createSQL(), 
            "session" => $app->session,
            "username" => $app->getUsername(), 
            "userId" => $app->getUserId()
        ])
        ->middlewares(
            [LoggedInMiddleware::class],
            [DisabledUserMiddleware::class]
        )
    );

    $app->router->addRoute((new Route())
        ->post("/contact", [PageController::class])
        ->middlewares([OnlyUserMiddleware::class])
    );

    $app->router->addRoute((new Route())
        ->post("/catalog", [CatalogController::class, 
            "sql" => $app->database->createSQL(), 
            "username" => $app->getUsername(), 
            "userId" => $app->getUserId()
        ])
        ->middlewares(
            [LoggedInMiddleware::class],
            [DisabledUserMiddleware::class]
        )
    );

    $app->router->addRoute((new Route())
        ->post("/cart", [CartController::class, 
            "sql" => $app->database->createSQL(), 
            "username" => $app->getUsername(), 
            "session" => $app->session,
            "userId" => $app->getUserId()
        ])
        ->middlewares(
            [LoggedInMiddleware::class],
            [DisabledUserMiddleware::class]
        )
    );

    $app->router->addRoute((new Route())
        ->post("/admin/users", [AdminUserController::class])
        ->middlewares(
            [DisabledUserMiddleware::class],
            [AdminMiddleware::class]
        )
    );

    $app->router->addRoute((new Route())
        ->post("/register/account", [AuthController::class])
    );

    $app->router->addRoute((new Route())
        ->post("/register/personal", [AuthController::class])
    );

    $app->router->addRoute((new Route())
        ->post("/register/delivery", [AuthController::class, 
            "login" => $app->login(...)
        ])
    );
    
    // API Routes
    $app->router->addRoute((new Route())
        ->post("/api/cart/update", [SyncCartController::class,
            "database" => $app->database, 
            "userId" => $app->getUserId()
        ])
        ->middlewares(
            [LoggedInMiddleware::class],
            [DisabledUserMiddleware::class]
        )
    );

    $app->router->addRoute((new Route())
        ->post("/api/admin/finance/orders", [FinanceDataController::class,
            "sql" => $app->database->createSQL()
        ])
        ->middlewares(
            [DisabledUserMiddleware::class],
            [AdminMiddleware::class]
        )
    );

    $app->router->addRoute((new Route())
        ->post("/api/admin/finance/sales", [FinanceDataController::class,
            "sql" => $app->database->createSQL()
        ])
        ->middlewares(
            [DisabledUserMiddleware::class],
            [AdminMiddleware::class]
        )
    );

    $app->router->addRoute((new Route())
        ->post("/api/admin/finance/head", [FinanceDataController::class,
            "sql" => $app->database->createSQL()
        ])
        ->middlewares(
            [DisabledUserMiddleware::class],
            [AdminMiddleware::class]
        )
    );
};