<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnitPriceToPurchaseRequestItems extends Migration
{
    public function up()
    {
        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->decimal('unit_price', 13, 2)->nullable()->after('quantity');
        });
    }

    public function down()
    {
        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->dropColumn('unit_price');
        });
    }
}
