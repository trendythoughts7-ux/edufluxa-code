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
        DB::statement('ALTER TABLE product_badge_contents ADD INDEX product_badge_contents_targetable_id_index (targetable_id)');
        DB::statement('ALTER TABLE product_categories ADD INDEX product_categories_parent_id_index (parent_id)');
        DB::statement('ALTER TABLE related_courses ADD INDEX related_courses_targetable_id_index (targetable_id)');
        DB::statement('ALTER TABLE related_posts ADD INDEX related_posts_targetable_id_index (targetable_id)');
        DB::statement('ALTER TABLE related_products ADD INDEX related_products_targetable_id_index (targetable_id)');
        DB::statement('ALTER TABLE reserve_meetings ADD INDEX reserve_meetings_meeting_id_index (meeting_id)');
        DB::statement('ALTER TABLE rewards_accounting ADD INDEX rewards_accounting_item_id_index (item_id)');
        DB::statement('ALTER TABLE sections ADD INDEX sections_section_group_id_index (section_group_id)');
        DB::statement('ALTER TABLE sessions ADD INDEX sessions_zoom_id_index (zoom_id)');
        DB::statement('ALTER TABLE specific_locations ADD INDEX specific_locations_targetable_id_index (targetable_id)');
        DB::statement('ALTER TABLE time_spent_on_courses ADD INDEX time_spent_on_courses_user_id_index (user_id)');
        DB::statement('ALTER TABLE users_manual_purchase ADD INDEX users_manual_purchase_user_id_index (user_id)');
        DB::statement('ALTER TABLE users_manual_purchase ADD INDEX users_manual_purchase_webinar_id_index (webinar_id)');
        DB::statement('ALTER TABLE users_zoom_api ADD INDEX users_zoom_api_account_id_index (account_id)');
        DB::statement('ALTER TABLE user_login_histories ADD INDEX user_login_histories_session_id_index (session_id)');
        DB::statement('ALTER TABLE visits_logs ADD INDEX visits_logs_owner_id_index (owner_id)');
        DB::statement('ALTER TABLE visits_logs ADD INDEX visits_logs_targetable_id_index (targetable_id)');
        DB::statement('ALTER TABLE visits_logs ADD INDEX visits_logs_visitor_id_index (visitor_id)');
        DB::statement('ALTER TABLE webinar_assignment_attachments ADD INDEX webinar_assignment_attachments_creator_id_index (creator_id)');
        DB::statement('ALTER TABLE webinar_assignment_history_messages ADD INDEX webinar_assignment_history_messages_sender_id_index (sender_id)');
        DB::statement('ALTER TABLE webinar_chapter_items ADD INDEX webinar_chapter_items_item_id_index (item_id)');
        DB::statement('ALTER TABLE webinar_reports ADD INDEX webinar_reports_user_id_index (user_id)');
        DB::statement('ALTER TABLE users ADD INDEX users_facebook_id_index (facebook_id)');
        DB::statement('ALTER TABLE users ADD INDEX users_google_id_index (google_id)');
        DB::statement('ALTER TABLE users ADD INDEX users_organ_id_index (organ_id)');
        DB::statement('ALTER TABLE users ADD INDEX users_role_id_index (role_id)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE product_badge_contents DROP INDEX product_badge_contents_targetable_id_index');
        DB::statement('ALTER TABLE product_categories DROP INDEX product_categories_parent_id_index');
        DB::statement('ALTER TABLE related_courses DROP INDEX related_courses_targetable_id_index');
        DB::statement('ALTER TABLE related_posts DROP INDEX related_posts_targetable_id_index');
        DB::statement('ALTER TABLE related_products DROP INDEX related_products_targetable_id_index');
        DB::statement('ALTER TABLE reserve_meetings DROP INDEX reserve_meetings_meeting_id_index');
        DB::statement('ALTER TABLE rewards_accounting DROP INDEX rewards_accounting_item_id_index');
        DB::statement('ALTER TABLE sections DROP INDEX sections_section_group_id_index');
        DB::statement('ALTER TABLE sessions DROP INDEX sessions_zoom_id_index');
        DB::statement('ALTER TABLE specific_locations DROP INDEX specific_locations_targetable_id_index');
        DB::statement('ALTER TABLE time_spent_on_courses DROP INDEX time_spent_on_courses_user_id_index');
        DB::statement('ALTER TABLE users_manual_purchase DROP INDEX users_manual_purchase_user_id_index');
        DB::statement('ALTER TABLE users_manual_purchase DROP INDEX users_manual_purchase_webinar_id_index');
        DB::statement('ALTER TABLE users_zoom_api DROP INDEX users_zoom_api_account_id_index');
        DB::statement('ALTER TABLE user_login_histories DROP INDEX user_login_histories_session_id_index');
        DB::statement('ALTER TABLE visits_logs DROP INDEX visits_logs_owner_id_index');
        DB::statement('ALTER TABLE visits_logs DROP INDEX visits_logs_targetable_id_index');
        DB::statement('ALTER TABLE visits_logs DROP INDEX visits_logs_visitor_id_index');
        DB::statement('ALTER TABLE webinar_assignment_attachments DROP INDEX webinar_assignment_attachments_creator_id_index');
        DB::statement('ALTER TABLE webinar_assignment_history_messages DROP INDEX webinar_assignment_history_messages_sender_id_index');
        DB::statement('ALTER TABLE webinar_chapter_items DROP INDEX webinar_chapter_items_item_id_index');
        DB::statement('ALTER TABLE webinar_reports DROP INDEX webinar_reports_user_id_index');
        DB::statement('ALTER TABLE users DROP INDEX users_facebook_id_index');
        DB::statement('ALTER TABLE users DROP INDEX users_google_id_index');
        DB::statement('ALTER TABLE users DROP INDEX users_organ_id_index');
        DB::statement('ALTER TABLE users DROP INDEX users_role_id_index');
    }
};
