<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlugToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // slug used in public API, unique for lookup
            $table->string('slug')->unique()->after('name')->nullable();
        });

        // populate slug for existing rows
        if (Schema::hasTable('products')) {
            \App\Models\Product::get()->each(function ($product) {
                $product->slug = \Illuminate\Support\Str::slug($product->name);
                $product->save();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
}
