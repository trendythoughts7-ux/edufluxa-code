<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE sales ADD INDEX sales_bundle_id_index (bundle_id)');
        DB::statement('ALTER TABLE sales ADD INDEX sales_event_ticket_id_index (event_ticket_id)');
        DB::statement('ALTER TABLE sales ADD INDEX sales_gift_id_index (gift_id)');
        DB::statement('ALTER TABLE sales ADD INDEX sales_meeting_package_id_index (meeting_package_id)');
        DB::statement('ALTER TABLE sales ADD INDEX sales_meeting_time_id_index (meeting_time_id)');
        DB::statement('ALTER TABLE sales ADD INDEX sales_product_order_id_index (product_order_id)');
        DB::statement('ALTER TABLE sales ADD INDEX sales_registration_package_id_index (registration_package_id)');
        DB::statement('ALTER TABLE sales ADD INDEX sales_subscribe_id_index (subscribe_id)');

        DB::statement('ALTER TABLE order_items ADD INDEX order_items_become_instructor_id_index (become_instructor_id)');
        DB::statement('ALTER TABLE order_items ADD INDEX order_items_bundle_id_index (bundle_id)');
        DB::statement('ALTER TABLE order_items ADD INDEX order_items_discount_id_index (discount_id)');
        DB::statement('ALTER TABLE order_items ADD INDEX order_items_event_ticket_id_index (event_ticket_id)');
        DB::statement('ALTER TABLE order_items ADD INDEX order_items_installment_payment_id_index (installment_payment_id)');
        DB::statement('ALTER TABLE order_items ADD INDEX order_items_meeting_package_id_index (meeting_package_id)');
        DB::statement('ALTER TABLE order_items ADD INDEX order_items_product_id_index (product_id)');
        DB::statement('ALTER TABLE order_items ADD INDEX order_items_product_order_id_index (product_order_id)');
        DB::statement('ALTER TABLE order_items ADD INDEX order_items_registration_package_id_index (registration_package_id)');
        DB::statement('ALTER TABLE order_items ADD INDEX order_items_user_id_index (user_id)');

        DB::statement('ALTER TABLE product_orders ADD INDEX product_orders_buyer_id_index (buyer_id)');
        DB::statement('ALTER TABLE product_orders ADD INDEX product_orders_discount_id_index (discount_id)');
        DB::statement('ALTER TABLE product_orders ADD INDEX product_orders_product_id_index (product_id)');
        DB::statement('ALTER TABLE product_orders ADD INDEX product_orders_sale_id_index (sale_id)');
        DB::statement('ALTER TABLE product_orders ADD INDEX product_orders_seller_id_index (seller_id)');

        DB::statement('ALTER TABLE accounting ADD INDEX accounting_bundle_id_index (bundle_id)');
        DB::statement('ALTER TABLE accounting ADD INDEX accounting_creator_id_index (creator_id)');
        DB::statement('ALTER TABLE accounting ADD INDEX accounting_event_ticket_id_index (event_ticket_id)');
        DB::statement('ALTER TABLE accounting ADD INDEX accounting_gift_id_index (gift_id)');
        DB::statement('ALTER TABLE accounting ADD INDEX accounting_installment_order_id_index (installment_order_id)');
        DB::statement('ALTER TABLE accounting ADD INDEX accounting_meeting_package_id_index (meeting_package_id)');
        DB::statement('ALTER TABLE accounting ADD INDEX accounting_order_item_id_index (order_item_id)');
        DB::statement('ALTER TABLE accounting ADD INDEX accounting_product_id_index (product_id)');
        DB::statement('ALTER TABLE accounting ADD INDEX accounting_referred_user_id_index (referred_user_id)');
        DB::statement('ALTER TABLE accounting ADD INDEX accounting_registration_package_id_index (registration_package_id)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE sales DROP INDEX sales_bundle_id_index');
        DB::statement('ALTER TABLE sales DROP INDEX sales_event_ticket_id_index');
        DB::statement('ALTER TABLE sales DROP INDEX sales_gift_id_index');
        DB::statement('ALTER TABLE sales DROP INDEX sales_meeting_package_id_index');
        DB::statement('ALTER TABLE sales DROP INDEX sales_meeting_time_id_index');
        DB::statement('ALTER TABLE sales DROP INDEX sales_product_order_id_index');
        DB::statement('ALTER TABLE sales DROP INDEX sales_registration_package_id_index');
        DB::statement('ALTER TABLE sales DROP INDEX sales_subscribe_id_index');

        DB::statement('ALTER TABLE order_items DROP INDEX order_items_become_instructor_id_index');
        DB::statement('ALTER TABLE order_items DROP INDEX order_items_bundle_id_index');
        DB::statement('ALTER TABLE order_items DROP INDEX order_items_discount_id_index');
        DB::statement('ALTER TABLE order_items DROP INDEX order_items_event_ticket_id_index');
        DB::statement('ALTER TABLE order_items DROP INDEX order_items_installment_payment_id_index');
        DB::statement('ALTER TABLE order_items DROP INDEX order_items_meeting_package_id_index');
        DB::statement('ALTER TABLE order_items DROP INDEX order_items_product_id_index');
        DB::statement('ALTER TABLE order_items DROP INDEX order_items_product_order_id_index');
        DB::statement('ALTER TABLE order_items DROP INDEX order_items_registration_package_id_index');
        DB::statement('ALTER TABLE order_items DROP INDEX order_items_user_id_index');

        DB::statement('ALTER TABLE product_orders DROP INDEX product_orders_buyer_id_index');
        DB::statement('ALTER TABLE product_orders DROP INDEX product_orders_discount_id_index');
        DB::statement('ALTER TABLE product_orders DROP INDEX product_orders_product_id_index');
        DB::statement('ALTER TABLE product_orders DROP INDEX product_orders_sale_id_index');
        DB::statement('ALTER TABLE product_orders DROP INDEX product_orders_seller_id_index');

        DB::statement('ALTER TABLE accounting DROP INDEX accounting_bundle_id_index');
        DB::statement('ALTER TABLE accounting DROP INDEX accounting_creator_id_index');
        DB::statement('ALTER TABLE accounting DROP INDEX accounting_event_ticket_id_index');
        DB::statement('ALTER TABLE accounting DROP INDEX accounting_gift_id_index');
        DB::statement('ALTER TABLE accounting DROP INDEX accounting_installment_order_id_index');
        DB::statement('ALTER TABLE accounting DROP INDEX accounting_meeting_package_id_index');
        DB::statement('ALTER TABLE accounting DROP INDEX accounting_order_item_id_index');
        DB::statement('ALTER TABLE accounting DROP INDEX accounting_product_id_index');
        DB::statement('ALTER TABLE accounting DROP INDEX accounting_referred_user_id_index');
        DB::statement('ALTER TABLE accounting DROP INDEX accounting_registration_package_id_index');
    }
};
