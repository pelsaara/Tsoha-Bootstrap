<?php

class ProductController extends BaseController {

    public static function listAll() {
        $products = Product::all();
        View::make('product/list.html', array('products' => $products));
    }

    public static function listAllByCategory($id) {
        $products = Product::findByCategory($id);
        View::make('category/show.html', array('products' => $products));
    }

    public static function listAllByBrand($id) {
        $products = Product::findByBrand($id);
        View::make('brand/show.html', array('products' => $products));
    }

    public static function show($id) {
        $product = Product::find($id);
        View::make('product/show.html', array('product' => $product));
    }

    public static function create() {
        self::check_logged_in();
        $brands = Brand::all();
        $categories = Category::all();
        View::make('product/new.html', array('brands' => $brands, 'categories' => $categories));
    }

    public static function store() {
        self::check_logged_in();
        $params = $_POST;

        $categories = $params['categories'];
        $attributes = array(
            'name' => $params['name'],
            'brand' => $params['brand'],
            'description' => $params['description'],
            'ingredients' => $params['ingredients'],
            'categories' => array()
        );

        foreach ($categories as $category) {
            $attributes['categories'][] = $category;
        }

        $product = new Product($attributes);

        $errors = $product->errors();
        if (count($errors) == 0) {
            $product->save();
            foreach ($categories as $category) {
                $pc = new ProductCategory(array('product_id' => $product->id, 'category_id' => $category));
                $pc->save();
            }
            Redirect::to('/product/' . $product->id, array('message' => 'Tuote on lisätty tietokantaan!'));
        } else {
            $brands = Brand::all();
            View::make('/product/new.html', array('errors' => $errors, 'attributes' => $attributes, 'brands' => $brands));
        }
    }

    public static function edit($id) {
        self::check_logged_in();
        $product = Product::find($id);
        $brands = Brand::all();
        $categories = Category::all();
        $productCategories = ProductCategory::productCategories($id);
        $pcIDs = array();
        foreach ($productCategories as $p) {
            $pcIDs[] = $p->category_id;
        }

        View::make('product/edit.html', array('attributes' => $product, 'brands' => $brands, 'categories' => $categories, 'productCategories' => $pcIDs));
    }

    public static function update($id) {
        self::check_logged_in();
        $params = $_POST;
        $categories = $params['categories'];

        $attributes = array(
            'id' => $id,
            'name' => $params['name'],
            'brand' => $params['brand'],
            'description' => $params['description'],
            'ingredients' => $params['ingredients'],
            'categories' => array()
        );

        foreach ($categories as $category) {
            $attributes['categories'][] = $category;
        }
        $product = new Product($attributes);
        $errors = $product->errors();

        if (count($errors) > 0) {
            $brands = Brand::all();
            $categories = Category::all();
            $productCategories = ProductCategory::productCategories($id);
            $pcIDs = array();
            foreach ($productCategories as $p) {
                $pcIDs[] = $p->category_id;
            }
            View::make('product/edit.html', array('errors' => $errors, 'attributes' => $product, 'brands' => $brands, 'categories' => $categories, 'productCategories' => $pcIDs));
        } else {
            $product->update();
            ProductCategory::destroyById($product->id);
            foreach ($categories as $category) {
                $pc = new ProductCategory(array('product_id' => $product->id, 'category_id' => $category));
                $pc->save();
            }
            Redirect::to('/product/' . $product->id, array('message' => 'Tuotetta on muokattu onnistuneesti!'));
        }
    }

    public static function destroy($id) {
        self::check_logged_in();
        $product = new Product(array('id' => $id));
        $product->delete();

        Redirect::to('/product', array('message' => 'Tuote on poistettu onnistuneesti!'));
    }

}
