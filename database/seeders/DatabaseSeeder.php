<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Create default admin
        if (!User::where('email', 'uter.vanan@gmail.com')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'uter.vanan@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]);
        }

        $this->createUsers();
        $this->createCategories();
        $this->createProducts();
        $this->createOrders();
    }

    public function createUsers()
    {
        $targetEmail = 'vanantran05@gmail.com';
        if (!User::where('email', $targetEmail)->exists()) {
            User::create([
                'name' => "An 05",
                'email' => $targetEmail,
                'password' => Hash::make('password'),
                'role' => 'user',
            ]);
        }


        for ($i = 1; $i <= 20; $i++) {
            $email = "user{$i}@example.com";

            if (!User::where('email', $email)->exists()) {
                User::create([
                    'name' => "User {$i}",
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role' => 'user',
                ]);
            }
        }
    }

    public function createCategories()
    {
        $categories = [
            'Áo Thun Nam',
            'Áo Thun Nữ',
            'Áo Sơ Mi Nam',
            'Áo Sơ Mi Nữ',
            'Quần Jeans Nam',
            'Quần Jeans Nữ',
            'Quần Short',
            'Váy Đầm',
            'Áo Khoác',
            'Áo Hoodie',
            'Áo Blazer',
            'Quần Tây',
            'Đồ Thể Thao',
            'Đồ Ngủ',
            'Đồ Lót',
            'Áo Len',
            'Áo Polo',
            'Set Bộ',
            'Đồ Công Sở',
            'Phụ Kiện Thời Trang'
        ];

        foreach ($categories as $categoryName) {
            Category::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                [
                    'name' => $categoryName,
                    'description' => "Danh mục {$categoryName}"
                ]
            );
        }
    }

    public function createProducts()
    {
        // Get all categories
        $categories = Category::all();

        if ($categories->isEmpty()) {
            return;
        }

        // Ensure category_id = 1 exists
        $categoryOne = Category::find(1);
        if (!$categoryOne) {
            return;
        }

        // Base product types
        $baseProducts = [
            'Áo Thun', 'Áo Sơ Mi', 'Quần Jeans', 'Quần Short',
            'Váy', 'Đầm', 'Áo Khoác', 'Áo Hoodie',
            'Áo Blazer', 'Quần Tây', 'Áo Len', 'Áo Polo',
            'Set Bộ', 'Đồ Thể Thao', 'Đồ Ngủ',
            'Áo Tanktop', 'Áo Cardigan', 'Quần Jogger',
            'Chân Váy', 'Áo Croptop'
        ];

        $adjectives = [
            'Basic', 'Cao Cấp', 'Premium', 'Slim Fit',
            'Oversize', 'Form Rộng', 'Hàn Quốc', 'Unisex',
            'Vintage', 'Hiện Đại', 'Thanh Lịch',
            'Trẻ Trung', 'Năng Động', 'Thời Trang',
            'Mùa Hè', 'Mùa Đông'
        ];

        $materials = [
            'Cotton', 'Lụa', 'Denim', 'Polyester',
            'Len', 'Thun Lạnh', 'Kaki', 'Voan'
        ];

        // Helper function to generate product name
        $generateName = function ($index) use ($baseProducts, $adjectives, $materials) {
            $base = collect($baseProducts)->random();
            $adj = collect($adjectives)->random();
            $material = collect($materials)->random();

            return "{$base} {$adj} {$material} {$index}";
        };

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Create 100 products for category_id = 1
        |--------------------------------------------------------------------------
        */
        for ($i = 1; $i <= 100; $i++) {

            $productName = $generateName("C1-{$i}");
            $slug = Str::slug($productName);

            Product::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $productName,
                    'price' => rand(150000, 1500000),
                    'description' => "Sản phẩm {$productName} chất lượng cao.",
                    'category_id' => 1,
                    'image' => 'default-product.jpg'
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ Create 100 products for random categories
        |--------------------------------------------------------------------------
        */
        for ($i = 1; $i <= 100; $i++) {

            $productName = $generateName("R-{$i}");
            $slug = Str::slug($productName);

            Product::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $productName,
                    'price' => rand(150000, 1500000),
                    'description' => "Sản phẩm {$productName} chất lượng cao.",
                    'category_id' => $categories->random()->id,
                    'image' => 'default-product.jpg'
                ]
            );
        }
    }

    public function createOrders()
    {
        $targetEmail = 'user1@example.com';
        $user = User::where('email', $targetEmail)->first();

        if (!$user) {
            return;
        }

        $products = Product::all();

        for ($i = 0; $i < 20; $i++) {

            DB::transaction(function () use ($user, $products) {

                $order = Order::create([
                    'user_id' => $user->id,
                    'total_amount' => 0,
                    'status' => collect(['pending', 'processing', 'completed'])->random()
                ]);

                $totalAmount = 0;

                $itemsCount = rand(1, 5);
                $selectedProducts = $products->random($itemsCount);

                foreach ($selectedProducts as $product) {

                    $quantity = rand(1, 3);
                    $price = $product->price;
                    $total = $price * $quantity;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'total' => $total,
                    ]);

                    $totalAmount += $total;
                }

                $order->update([
                    'total_amount' => $totalAmount
                ]);
            });
        }
    }
}
