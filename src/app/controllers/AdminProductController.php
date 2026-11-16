<?php
namespace app\controllers;

use app\models\database\ProductImages;
use app\models\database\Products;
use Override;
use PDO;
use core\controller\Controller;
use core\data\SQLStatement;
use core\web\Request;
use core\web\Response;
use function count;

class AdminProductController extends Controller {
    #[Override]
    public function index(Request $request): Response {
        $body = $request->body();
        $products = $this->queryProducts($body["search"] ?? null);

        $body["products"] = $products;
        $body["categories"] = $this->queryCategories();
        $body["section"] = "Products";
        if ($request->isGet()) {
            if (isset($body["editProduct"])) {
                $productId = filter_var($body["editProduct"], FILTER_VALIDATE_INT);
                if ($productId === false || $productId < 1) return Response::redirectOnly("/admin/products");

                $product = Products::findOne(["productId", "=", $productId]);
                if ($product === false) return Response::redirectOnly("/admin/products");
                $body["model"] = $product;

                $productImage = ProductImages::findOne(["productId", "=", $productId]);
                if ($productImage !== false) {
                    $body["productImagePath"] = $productImage->imagePath;
                }
            }
            return Response::create()
                ->contentFrom("/admin/products", "admin", $body);
        }

        // POST Request
        error_log(print_r($body, true));
        if ($body["action"] === "save") {
            $product = new Products();
            $product->loadData($body);
            if (isset($body["editProduct"])) {
                if ($body["editProduct"] === false || $body["editProduct"] < 1) return Response::redirectOnly("/admin/products");
                $product->loadData([
                    "productId" => $body["editProduct"]
                ]);
            }

            if (!$product->validate()) {
                $body["model"] = $product;
                if (isset($body["editProduct"])) {
                    $productImage = ProductImages::findOne(["productId", "=", $body["editProduct"]]);
                    if ($productImage !== false) {
                        $body["productImagePath"] = $productImage->imagePath;
                    }
                }
            } else {
                if (!isset($body["editProduct"])) {
                    $product->insert();
                    $productId = $product->productId;
                } else {
                    $product->update();
                    $productId = (int) $body["editProduct"];
                }

                $imageFile = $this->safeUpload($request);
                if ($imageFile !== "") {
                    $foundImage = ProductImages::findOne(["productId", "=", $productId]);

                    if ($foundImage === false) {
                        $productImage = new ProductImages();
                        $productImage->loadData([
                            "productId" => $productId,
                            "imagePath" => $imageFile
                        ]);
                        $productImage->insert();
                    } else {
                        $foundImage->loadData(["imagePath" => $imageFile]);
                        $foundImage->update();
                    }
                }
            }
        } else if ($body["action"] === "delete") {
            if (!isset($body["editProduct"])) return Response::redirectOnly("/admin/products");
            $productImage = ProductImages::findOne(["productId", "=", $body["editProduct"]]);
            if ($productImage) $productImage->delete();

            $product = Products::findOne(["productId", "=", $body["editProduct"]]);
            if ($product) $product->delete();

            $remaining = count($products) - 1;
            $lastPage = max(1, (int) ceil($remaining / 12));
            $page = max(1, (int) ($body["page"] ?? 1));
            if ($page > $lastPage) {
                $body["page"] = $lastPage;
            }
        }

        $redirectParams = array_intersect_key($body, array_flip(["page", "search"]));
        $redirectUrl = "/admin/products";
        if ($redirectParams !== []) {
            $redirectUrl .= "?" . http_build_query($redirectParams);
        }
        return Response::redirectOnly($redirectUrl);
    }

    private function queryProducts(?string $search): array {
        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];
        $sql = $sql
            ->select([
                "Products.productId",
                "Products.productName",
                "Products.productPrice",
                "Products.stock",
                "Products.created_at",
                "Categories.categoryName",
                "ProductImages.imagePath"
            ])
            ->from("Products")
            ->innerJoin("Categories", ["Products.categoryId" => "Categories.categoryId"])
            ->leftJoin("ProductImages", ["Products.productId" => "ProductImages.productId"]);

        if (isset($search)) {
            $sql = $sql->where("Products.productName", "LIKE", "%{$search}%");
        }
        
        return $sql->groupBy(["Products.productId"])
            ->query()
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    private function queryCategories(): array {
        /** @var SQLStatement */
        $sql = $this->injectedData["sql"];
        return $sql
            ->select([
                "categoryId",
                "categoryName"
            ])
            ->from("Categories")
            ->query()
            ->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    private function safeUpload(Request $request): string {
        $uploads = $request->uploads(["png", "jpg", "jpeg"], ["image/jpeg", "image/png"]);
        if (empty($uploads)) return "";
        $productImages = $uploads["productImages"];
        $uploadDir = ROOT . "/public/uploads/products";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileExtension = strtolower(pathinfo($productImages["name"], PATHINFO_EXTENSION));
        $newFile = uniqid() . "." . $fileExtension;
        $filePath = "{$uploadDir}/{$newFile}";
        if (move_uploaded_file($productImages["tmp_name"], $filePath)) {
            return "/lianmart/uploads/products/{$newFile}";
        } else {
            error_log("Failed to move uploaded file: " . $productImages["tmp_name"] . " to " . $filePath);
            return "";
        }
    }
}