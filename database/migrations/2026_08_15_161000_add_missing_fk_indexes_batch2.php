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
        DB::statement('ALTER TABLE abandoned_cart_rule_histories ADD INDEX abandoned_cart_rule_histories_cart_rule_id_index (cart_rule_id)');
        DB::statement('ALTER TABLE abandoned_cart_rule_histories ADD INDEX abandoned_cart_rule_histories_user_id_index (user_id)');
        DB::statement('ALTER TABLE become_instructors ADD INDEX become_instructors_package_id_index (package_id)');
        DB::statement('ALTER TABLE bundle_webinars ADD INDEX bundle_webinars_creator_id_index (creator_id)');
        DB::statement('ALTER TABLE comments ADD INDEX comments_product_review_id_index (product_review_id)');
        DB::statement('ALTER TABLE comments_reports ADD INDEX comments_reports_blog_id_index (blog_id)');
        DB::statement('ALTER TABLE comments_reports ADD INDEX comments_reports_bundle_id_index (bundle_id)');
        DB::statement('ALTER TABLE comments_reports ADD INDEX comments_reports_user_id_index (user_id)');
        DB::statement('ALTER TABLE comments_reports ADD INDEX comments_reports_webinar_id_index (webinar_id)');
        DB::statement('ALTER TABLE content_delete_requests ADD INDEX content_delete_requests_targetable_id_index (targetable_id)');
        DB::statement('ALTER TABLE course_learning_last_views ADD INDEX course_learning_last_views_item_id_index (item_id)');
        DB::statement('ALTER TABLE course_noticeboard_status ADD INDEX course_noticeboard_status_user_id_index (user_id)');
        DB::statement('ALTER TABLE course_personal_notes ADD INDEX course_personal_notes_targetable_id_index (targetable_id)');
        DB::statement('ALTER TABLE event_reports ADD INDEX event_reports_user_id_index (user_id)');
        DB::statement('ALTER TABLE event_tickets_sold ADD INDEX event_tickets_sold_event_ticket_id_index (event_ticket_id)');
        DB::statement('ALTER TABLE event_tickets_sold ADD INDEX event_tickets_sold_sale_id_index (sale_id)');
        DB::statement('ALTER TABLE event_tickets_sold ADD INDEX event_tickets_sold_user_id_index (user_id)');
        DB::statement('ALTER TABLE forums ADD INDEX forums_parent_id_index (parent_id)');
        DB::statement('ALTER TABLE forum_topic_visits ADD INDEX forum_topic_visits_user_id_index (user_id)');
        DB::statement('ALTER TABLE installment_reminders ADD INDEX installment_reminders_installment_order_id_index (installment_order_id)');
        DB::statement('ALTER TABLE installment_reminders ADD INDEX installment_reminders_installment_step_id_index (installment_step_id)');
        DB::statement('ALTER TABLE newsletters ADD INDEX newsletters_user_id_index (user_id)');
        DB::statement('ALTER TABLE noticeboards_status ADD INDEX noticeboards_status_user_id_index (user_id)');
        DB::statement('ALTER TABLE notifications ADD INDEX notifications_sender_id_index (sender_id)');
        DB::statement('ALTER TABLE notifications_status ADD INDEX notifications_status_user_id_index (user_id)');
        DB::statement('ALTER TABLE orders ADD INDEX orders_reference_id_index (reference_id)');
        DB::statement('ALTER TABLE payu_transactions ADD INDEX payu_transactions_paid_for_id_index (paid_for_id)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE abandoned_cart_rule_histories DROP INDEX abandoned_cart_rule_histories_cart_rule_id_index');
        DB::statement('ALTER TABLE abandoned_cart_rule_histories DROP INDEX abandoned_cart_rule_histories_user_id_index');
        DB::statement('ALTER TABLE become_instructors DROP INDEX become_instructors_package_id_index');
        DB::statement('ALTER TABLE bundle_webinars DROP INDEX bundle_webinars_creator_id_index');
        DB::statement('ALTER TABLE comments DROP INDEX comments_product_review_id_index');
        DB::statement('ALTER TABLE comments_reports DROP INDEX comments_reports_blog_id_index');
        DB::statement('ALTER TABLE comments_reports DROP INDEX comments_reports_bundle_id_index');
        DB::statement('ALTER TABLE comments_reports DROP INDEX comments_reports_user_id_index');
        DB::statement('ALTER TABLE comments_reports DROP INDEX comments_reports_webinar_id_index');
        DB::statement('ALTER TABLE content_delete_requests DROP INDEX content_delete_requests_targetable_id_index');
        DB::statement('ALTER TABLE course_learning_last_views DROP INDEX course_learning_last_views_item_id_index');
        DB::statement('ALTER TABLE course_noticeboard_status DROP INDEX course_noticeboard_status_user_id_index');
        DB::statement('ALTER TABLE course_personal_notes DROP INDEX course_personal_notes_targetable_id_index');
        DB::statement('ALTER TABLE event_reports DROP INDEX event_reports_user_id_index');
        DB::statement('ALTER TABLE event_tickets_sold DROP INDEX event_tickets_sold_event_ticket_id_index');
        DB::statement('ALTER TABLE event_tickets_sold DROP INDEX event_tickets_sold_sale_id_index');
        DB::statement('ALTER TABLE event_tickets_sold DROP INDEX event_tickets_sold_user_id_index');
        DB::statement('ALTER TABLE forums DROP INDEX forums_parent_id_index');
        DB::statement('ALTER TABLE forum_topic_visits DROP INDEX forum_topic_visits_user_id_index');
        DB::statement('ALTER TABLE installment_reminders DROP INDEX installment_reminders_installment_order_id_index');
        DB::statement('ALTER TABLE installment_reminders DROP INDEX installment_reminders_installment_step_id_index');
        DB::statement('ALTER TABLE newsletters DROP INDEX newsletters_user_id_index');
        DB::statement('ALTER TABLE noticeboards_status DROP INDEX noticeboards_status_user_id_index');
        DB::statement('ALTER TABLE notifications DROP INDEX notifications_sender_id_index');
        DB::statement('ALTER TABLE notifications_status DROP INDEX notifications_status_user_id_index');
        DB::statement('ALTER TABLE orders DROP INDEX orders_reference_id_index');
        DB::statement('ALTER TABLE payu_transactions DROP INDEX payu_transactions_paid_for_id_index');
    }
};
