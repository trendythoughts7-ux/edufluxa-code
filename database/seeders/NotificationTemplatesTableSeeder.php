<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationTemplatesTableSeeder extends Seeder
{
    public function run()
    {
        $templates = array (
  0 => 
  array (
    'id' => 2,
    'title' => 'New badge awarded',
    'template' => '<p>You received [u.b.title]&nbsp;badge</p>',
  ),
  1 => 
  array (
    'id' => 3,
    'title' => 'User group change',
    'template' => '<p>Your user group changed to [u.g.title]</p>',
  ),
  2 => 
  array (
    'id' => 4,
    'title' => 'Course created',
    'template' => '<p>You created a new course&nbsp;with title [c.title]</p>',
  ),
  3 => 
  array (
    'id' => 5,
    'title' => 'Course approve',
    'template' => '<p>Your course with title [c.title] approved</p>',
  ),
  4 => 
  array (
    'id' => 6,
    'title' => 'Course rejection',
    'template' => '<p>Your course with title [c.title] rejected</p>',
  ),
  5 => 
  array (
    'id' => 7,
    'title' => 'New comment',
    'template' => '<p>[u.name] left a new comment for [c.title] course</p>',
  ),
  6 => 
  array (
    'id' => 8,
    'title' => 'New support message',
    'template' => '<p>[u.name] sent a new support message for [c.title]&nbsp;course</p>',
  ),
  7 => 
  array (
    'id' => 9,
    'title' => 'Support message replied',
    'template' => '<p>New reply in [c.title] course support message&nbsp;</p>',
  ),
  8 => 
  array (
    'id' => 10,
    'title' => 'New support for admin',
    'template' => '<p>New support ticket received with title [s.t.title]</p>',
  ),
  9 => 
  array (
    'id' => 11,
    'title' => 'Support ticket replied for admin',
    'template' => '<p>New reply in support ticket with title&nbsp;[s.t.title]</p>',
  ),
  10 => 
  array (
    'id' => 12,
    'title' => 'New financial document',
    'template' => '<p>&nbsp;New financial document submitted for [c.title] with type [f.d.type] with amount [amount]</p>',
  ),
  11 => 
  array (
    'id' => 13,
    'title' => 'Payout request',
    'template' => '<p>New payout request submitted with amount [payout.amount]</p>',
  ),
  12 => 
  array (
    'id' => 14,
    'title' => 'Payout processed',
    'template' => 'Your payout request with amount [payout.amount]&nbsp;&nbsp;proceed to [payout.account]',
  ),
  13 => 
  array (
    'id' => 15,
    'title' => 'New sales',
    'template' => '<p>Congratulations! New sale for [c.title]</p>',
  ),
  14 => 
  array (
    'id' => 16,
    'title' => 'New purchase',
    'template' => '<p>Congratulations! New purchase for [c.title]</p>',
  ),
  15 => 
  array (
    'id' => 17,
    'title' => 'Rating (Feedback)',
    'template' => '<p>New [rate.count] star feedback submitted for [c.title] by [student.name]</p>',
  ),
  16 => 
  array (
    'id' => 18,
    'title' => 'Offline payment request',
    'template' => '<p>The offline payment request with the amount [amount] submitted. It is under review and you will get informed by email.</p>',
  ),
  17 => 
  array (
    'id' => 19,
    'title' => 'Offline payment approved',
    'template' => '<p>Offline payment request with amount [amount]&nbsp;approved</p>',
  ),
  18 => 
  array (
    'id' => 20,
    'title' => 'Offline payment rejected',
    'template' => '<p>Offline payment request with amount [amount]&nbsp;rejected</p>',
  ),
  19 => 
  array (
    'id' => 21,
    'title' => 'New subscription plan',
    'template' => '<p>[s.p.name] subscription plan activated by [u.name]</p>',
  ),
  20 => 
  array (
    'id' => 22,
    'title' => 'New meeting',
    'template' => '<p>New meeting booked by [u.name] for [time.date] at [amount]</p>',
  ),
  21 => 
  array (
    'id' => 23,
    'title' => 'New meeting link',
    'template' => '<p>[instructor.name] defined the meeting link and you can join the meeting on [time.date] using the following link: [link]</p>',
  ),
  22 => 
  array (
    'id' => 24,
    'title' => 'Meeting reminder',
    'template' => '<p>You have a meeting on [time.date] please remember to join it on time.</p>',
  ),
  23 => 
  array (
    'id' => 25,
    'title' => 'Meeting finished',
    'template' => '<p>Your meeting finished with the following information</p><p>Instructor: [instructor.name]</p><p>Student: [student.name]</p><p>Meeting time: [time.date]</p>',
  ),
  24 => 
  array (
    'id' => 26,
    'title' => 'New contact message',
    'template' => '<p>New contact message received from [u.name] with title [c.u.title]</p><p><br></p>',
  ),
  25 => 
  array (
    'id' => 27,
    'title' => 'Live class reminder',
    'template' => '<p>Your live class session of the [c.title] will be conducted on [time.date]&nbsp;</p>',
  ),
  26 => 
  array (
    'id' => 28,
    'title' => 'Promotion plan',
    'template' => '<p>[p.p.name] promotion plan activated for [c.title] course</p>',
  ),
  27 => 
  array (
    'id' => 29,
    'title' => 'Promotion plan for admin',
    'template' => '<p>[p.p.name] promotion plan request submitted for [c.title]</p>',
  ),
  28 => 
  array (
    'id' => 30,
    'title' => 'Certificate achieved',
    'template' => '<p>You achieved a certificate for [c.title] course</p>',
  ),
  29 => 
  array (
    'id' => 31,
    'title' => 'Waiting quiz (Instructor)',
    'template' => '<p>[student.name] is waiting for [q.title] quiz result of the [c.title] course. Please review the quiz and submit the grade.</p>',
  ),
  30 => 
  array (
    'id' => 32,
    'title' => 'Waiting quiz result',
    'template' => '<p>Your [q.title] quiz of the [c.title] course rated by the instructor, and your quiz status is [q.result]</p>',
  ),
  31 => 
  array (
    'id' => 33,
    'title' => 'Product new sale',
    'template' => '<p>New sale for [p.title] product</p>',
  ),
  32 => 
  array (
    'id' => 34,
    'title' => 'Product new purchase',
    'template' => '<p>New purchase for [p.title] product</p>',
  ),
  33 => 
  array (
    'id' => 35,
    'title' => 'Product new comment',
    'template' => '<p>[u.name] left a new comment for [p.title] product</p>',
  ),
  34 => 
  array (
    'id' => 36,
    'title' => 'Product tracking code',
    'template' => '<p>[u.name] submitted tracking code for [p.title]</p>',
  ),
  35 => 
  array (
    'id' => 37,
    'title' => 'Product rating (Feedback)',
    'template' => '<p>[u.name] submitted a new [rate.count] stars rating for [p.title] product</p>',
  ),
  36 => 
  array (
    'id' => 38,
    'title' => 'Product received',
    'template' => '<p>[u.name] received [p.title] product.</p>',
  ),
  37 => 
  array (
    'id' => 39,
    'title' => 'Product out of stock',
    'template' => '<p>Your product [p.title] is out of stock</p>',
  ),
  38 => 
  array (
    'id' => 40,
    'title' => 'Assignment submission (Instructor)',
    'template' => '<p>[student.name] submitted an assignment for [c.title] course</p>',
  ),
  39 => 
  array (
    'id' => 41,
    'title' => 'Instructor message in assignment',
    'template' => '<p>[instructor.name] sent a message for [c.title] assignment</p>',
  ),
  40 => 
  array (
    'id' => 42,
    'title' => 'Assignment grade',
    'template' => '<p>Your assignment of [c.title] rated by [instructor.name] . Your grade is [assignment_grade]</p>',
  ),
  41 => 
  array (
    'id' => 43,
    'title' => 'User access to content',
    'template' => '<p>Your access to content is enabled.</p>',
  ),
  42 => 
  array (
    'id' => 44,
    'title' => 'Send post in topic',
    'template' => '<p>[u.name] sent a post in your topic with title [topic_title]&nbsp;</p>',
  ),
  43 => 
  array (
    'id' => 45,
    'title' => 'Blog post published (Instructor)',
    'template' => '<p>Your blog post with title [blog_title] published.</p>',
  ),
  44 => 
  array (
    'id' => 46,
    'title' => 'New comment for blog post (Instructor)',
    'template' => '<p>[u.name] leaft a new comment for your blog with title [blog_title]</p>',
  ),
  45 => 
  array (
    'id' => 47,
    'title' => 'Meeting reminder',
    'template' => '<p>You have a meeting on [time.date] with [instructor.name]</p>',
  ),
  46 => 
  array (
    'id' => 48,
    'title' => 'Subscription expiry reminder',
    'template' => '<p>Your subscription expires on [time.date]&nbsp;</p>',
  ),
  47 => 
  array (
    'id' => 49,
    'title' => 'Course forum new question',
    'template' => '<p>[u.name] registered a question in the [c.title]&nbsp;course forum.</p>',
  ),
  48 => 
  array (
    'id' => 50,
    'title' => 'New answer in course forum',
    'template' => '<p>[u.name] submitted an answer in the [c.title]&nbsp;course forum.</p>',
  ),
  49 => 
  array (
    'id' => 52,
    'title' => 'You received a gift',
    'template' => '<p>[u.name]&nbsp;sent you [gift_title] which is a [gift_type]&nbsp;as a gift with the following message: [gift_message]</p>',
  ),
  50 => 
  array (
    'id' => 53,
    'title' => 'Gift submitted successfully',
    'template' => '<p>Your gift request for [u.name]&nbsp;submitted successfully on [time.date]&nbsp;and the [gift_title] which is a [gift_type]&nbsp;at [amount]&nbsp;will be sent to the recipient on [time.date.2]&nbsp;with the following message: [gift_message]</p>',
  ),
  51 => 
  array (
    'id' => 54,
    'title' => 'Gift sent to recipient',
    'template' => '<p>We sent the gift request that you submitted on [time.date]&nbsp;for [u.name]. We sent [gift_title]&nbsp;which is a [gift_type]&nbsp;to the recipient with the following message on [time.date] . [gift_message]</p>',
  ),
  52 => 
  array (
    'id' => 55,
    'title' => 'Gift request submitted (Admin)',
    'template' => '<p>[u.name.2] submitted a gift request for [gift_title]&nbsp;which is a [gift_type]&nbsp;for [u.name]&nbsp;on [time.date]&nbsp;at [amount]&nbsp;and it will be sent to the recipient on [time.date.2]</p>',
  ),
  53 => 
  array (
    'id' => 56,
    'title' => 'Gift sent to recipient (Admin)',
    'template' => '<p>The system sent a [gift_title]&nbsp;which is a [gift_type]&nbsp;to [u.name]&nbsp;on [time.date.2]&nbsp;successfully. [u.name.2]&nbsp;submitted this request on [time.date]&nbsp;at [amount].</p>',
  ),
  54 => 
  array (
    'id' => 57,
    'title' => 'You have an upcoming installment',
    'template' => '<p>You have an installment for [installment_title] at [amount]&nbsp;on due date [time.date]</p>',
  ),
  55 => 
  array (
    'id' => 58,
    'title' => 'You have an unpaid installment',
    'template' => '<p>You have an installment for [installment_title]&nbsp;at [amount]&nbsp;for today. Please pay it as soon as possible.</p>',
  ),
  56 => 
  array (
    'id' => 59,
    'title' => 'You have an overdue installment',
    'template' => '<p>You have an overdue installment for [installment_title]&nbsp;at [amount]&nbsp;on due date [time.date].</p>',
  ),
  57 => 
  array (
    'id' => 60,
    'title' => 'Installment verification request approved',
    'template' => '<p>Your verification request for [installment_title]&nbsp;approved.</p>',
  ),
  58 => 
  array (
    'id' => 61,
    'title' => 'Installment verification request rejected',
    'template' => '<p>Your verification request for [installment_title]&nbsp;rejected.</p>',
  ),
  59 => 
  array (
    'id' => 62,
    'title' => 'Installment paid successfully',
    'template' => '<p>You paid [amount]&nbsp;for [installment_title]&nbsp;with due date [time.date]&nbsp;successfully.</p>',
  ),
  60 => 
  array (
    'id' => 63,
    'title' => 'Installment paid successfully (Admin)',
    'template' => '<p>[u.name] paid [amount]&nbsp;for [installment_title]&nbsp;with the due date [time.date]&nbsp;successfully.</p>',
  ),
  61 => 
  array (
    'id' => 64,
    'title' => 'Installment upfront amount paid',
    'template' => '<p>You paid [amount] as upfront for&nbsp;[installment_title].</p>',
  ),
  62 => 
  array (
    'id' => 65,
    'title' => 'Installment verification request submitted',
    'template' => '<p>We received your verification request for [installment_title]&nbsp;on [time.date]&nbsp;and the result will be informed to you soon.</p>',
  ),
  63 => 
  array (
    'id' => 66,
    'title' => 'Installment verification request submitted (Admin)',
    'template' => '<p>[u.name] submitted a verification request for [installment_title]&nbsp;on [time.date].</p>',
  ),
  64 => 
  array (
    'id' => 67,
    'title' => 'Installment request submitted',
    'template' => '<p>Your installment for [installment_title]&nbsp;at [amount]&nbsp;submitted successfully.</p>',
  ),
  65 => 
  array (
    'id' => 68,
    'title' => 'Installment request submitted (Admin)',
    'template' => '<p>[u.name] submitted an installment request for [installment_title]&nbsp;at [amount].</p>',
  ),
  66 => 
  array (
    'id' => 69,
    'title' => 'New upcoming course submitted',
    'template' => '<p>Your upcoming course [item_title]&nbsp;submitted successfully.</p>',
  ),
  67 => 
  array (
    'id' => 70,
    'title' => 'New upcoming course submitted (Admin)',
    'template' => '<p>[u.name] submitted an upcoming course with title [item_title].</p>',
  ),
  68 => 
  array (
    'id' => 71,
    'title' => 'Upcoming course approved',
    'template' => '<p>Your upcoming course [item_title]&nbsp;approved.</p>',
  ),
  69 => 
  array (
    'id' => 72,
    'title' => 'Upcoming course rejected',
    'template' => '<p>Your upcoming course [item_title] rejected.</p>',
  ),
  70 => 
  array (
    'id' => 73,
    'title' => 'Your upcoming course published',
    'template' => '<p>Your upcoming course [item_title]&nbsp;published.</p>',
  ),
  71 => 
  array (
    'id' => 74,
    'title' => 'Your upcoming course followed',
    'template' => '<p>[u.name] followed your upcoming course [item_title]</p>',
  ),
  72 => 
  array (
    'id' => 75,
    'title' => 'Upcoming course published and is accessible',
    'template' => '<p>The upcoming course [item_title] published now and you can check it.</p>',
  ),
  73 => 
  array (
    'id' => 76,
    'title' => 'You got cashback!',
    'template' => '<p>You got [amount]&nbsp;as cashback and this amount added to your account.</p>',
  ),
  74 => 
  array (
    'id' => 77,
    'title' => 'User got cashback (Admin)',
    'template' => '<p>[u.name] got [amount] as cashback and this amount charged to their account.</p>',
  ),
  75 => 
  array (
    'id' => 78,
    'title' => 'Bundle submitted successfully',
    'template' => '<p>Your bundle with the title [item_title]&nbsp;submitted successfully.</p>',
  ),
  76 => 
  array (
    'id' => 79,
    'title' => 'Bundle submitted (Admin)',
    'template' => '<p>[u.name] submitted a bundle with the title [item_title].</p>',
  ),
  77 => 
  array (
    'id' => 80,
    'title' => 'Bundle published successfully',
    'template' => '<p>Your bundle with title [item_title]&nbsp;published successfully.</p>',
  ),
  78 => 
  array (
    'id' => 81,
    'title' => 'Bundle rejected',
    'template' => '<p>Your bundle with title [item_title]&nbsp;rejected.</p>',
  ),
  79 => 
  array (
    'id' => 82,
    'title' => 'New review for your bundle',
    'template' => '<p>[u.name] submitted a [rate.count] star rating for your bundle [item_title].</p>',
  ),
  80 => 
  array (
    'id' => 83,
    'title' => 'You got registration bonus',
    'template' => '<p>You got [amount]&nbsp;as registration bonus.</p>',
  ),
  81 => 
  array (
    'id' => 84,
    'title' => 'Registration bonus unlocked',
    'template' => '<p>Your registration bonus [amount]&nbsp;unlocked. Happy with spending...</p>',
  ),
  82 => 
  array (
    'id' => 85,
    'title' => 'Registration bonus unlocked (Admin)',
    'template' => '<p>The registration bonus [amount] unlocked for [u.name].</p>',
  ),
  83 => 
  array (
    'id' => 86,
    'title' => 'SaaS package activated successfully',
    'template' => '<p>[item_title] activated for you until [time.date].</p>',
  ),
  84 => 
  array (
    'id' => 87,
    'title' => 'SaaS package activated (Admin)',
    'template' => '<p>[u.name] activated [item_title]&nbsp;registration plan until [time.date].</p>',
  ),
  85 => 
  array (
    'id' => 88,
    'title' => 'Your contact message submitted',
    'template' => '<p>We received your contact message with the subject [c.u.title]&nbsp;on [time.date].</p>',
  ),
  86 => 
  array (
    'id' => 89,
    'title' => 'New contact message received',
    'template' => '<p>New contact message received from [u.name] with subject [c.u.title] with message [c.u.message]</p>',
  ),
  87 => 
  array (
    'id' => 90,
    'title' => 'You submitted to waitlist',
    'template' => '<p>You submitted to [c.title]&nbsp;waitlist.</p>',
  ),
  88 => 
  array (
    'id' => 91,
    'title' => 'User submitted in waitlist',
    'template' => '<p>[u.name] submitted to [c.title]&nbsp;waitlist.</p>',
  ),
  89 => 
  array (
    'id' => 92,
    'title' => 'New user registered with your affiliate code',
    'template' => '<p>[u.name] registered with your affiliate code on [time.date].</p>',
  ),
  90 => 
  array (
    'id' => 93,
    'title' => 'New quiz added to course',
    'template' => '<p>New quiz with the title [q.title]&nbsp;added to the course [c.title].</p>',
  ),
  91 => 
  array (
    'id' => 94,
    'title' => 'New reward point',
    'template' => '<p>You collected [points]&nbsp;for [item_title]&nbsp;on [time.date]</p>',
  ),
  92 => 
  array (
    'id' => 95,
    'title' => 'New notice',
    'template' => '<p>You got a new notice with title [c.title]&nbsp;on [time.date]</p>',
  ),
  93 => 
  array (
    'id' => 96,
    'title' => 'New course notice',
    'template' => '<p>You got a new course notice for [c.title]&nbsp;with title [item_title]</p>',
  ),
  94 => 
  array (
    'id' => 97,
    'title' => 'Your user role changed',
    'template' => '<p>Your user role changed to [u.role]</p>',
  ),
  95 => 
  array (
    'id' => 98,
    'title' => 'New user group',
    'template' => '<p>You added to [u.g.title] user group.</p>',
  ),
  96 => 
  array (
    'id' => 99,
    'title' => 'Become instructor/organization request approved',
    'template' => '<p>Your become instructor/organization request is approved.</p>',
  ),
  97 => 
  array (
    'id' => 100,
    'title' => 'Become instructor/organization request rejected',
    'template' => '<p>Your instructor/organization request rejected</p>',
  ),
  98 => 
  array (
    'id' => 101,
    'title' => 'New question in course forum',
    'template' => '<p>[u.name] posted a new question in [c.title] forum.</p>',
  ),
  99 => 
  array (
    'id' => 102,
    'title' => 'New answer in course forum',
    'template' => '<p>[u.name] posted a new answer in [c.title] forum.</p>',
  ),
  100 => 
  array (
    'id' => 103,
    'title' => 'Live meeting created',
    'template' => '<p>[instructor.name] started a new live meeting. Please login to your account and join it now...</p>',
  ),
  101 => 
  array (
    'id' => 104,
    'title' => 'New user registered',
    'template' => '<p>[u.name] registered on the platform on [time.date]&nbsp;as [u.role]</p>',
  ),
  102 => 
  array (
    'id' => 105,
    'title' => 'New instructor/organization request',
    'template' => '<p>[u.name] submitted a user role change request on [time.date]</p>',
  ),
  103 => 
  array (
    'id' => 106,
    'title' => 'New course enrollment',
    'template' => '<p>[u.name] enrolled in [c.title]&nbsp;on [time.date]&nbsp;at [amount]</p>',
  ),
  104 => 
  array (
    'id' => 107,
    'title' => 'New forum topic',
    'template' => '<p>[u.name] created a new topic with title [topic_title]&nbsp;in [forum_title]&nbsp;forum.</p>',
  ),
  105 => 
  array (
    'id' => 108,
    'title' => 'New report',
    'template' => '<p>[u.name] reported a content for revising.</p>',
  ),
  106 => 
  array (
    'id' => 109,
    'title' => 'New item created',
    'template' => '<p>[u.name] created a new item with title [item_title]</p>',
  ),
  107 => 
  array (
    'id' => 110,
    'title' => 'New store order',
    'template' => '<p>New store order received from [u.name]&nbsp;at [amount]</p>',
  ),
  108 => 
  array (
    'id' => 111,
    'title' => 'Subscription plan activated',
    'template' => '<p>[u.name] purchased [s.p.name]&nbsp;at [amount]</p>',
  ),
  109 => 
  array (
    'id' => 112,
    'title' => 'Content review request',
    'template' => '<p>[u.name] sent a review request for [item_title]</p>',
  ),
  110 => 
  array (
    'id' => 113,
    'title' => 'New user blog post',
    'template' => '<p>[u.name] submitted a blog article with title [blog_title]</p>',
  ),
  111 => 
  array (
    'id' => 114,
    'title' => 'New item review (Rating)',
    'template' => '<p>[u.name] submitted a new rate for [item_title]</p>',
  ),
  112 => 
  array (
    'id' => 115,
    'title' => 'New organization user',
    'template' => '<p>[organization.name] submitted [u.name]&nbsp;as new [u.role]</p>',
  ),
  113 => 
  array (
    'id' => 116,
    'title' => 'User wallet charge',
    'template' => '<p>[u.name] charged their wallet for [amount]</p>',
  ),
  114 => 
  array (
    'id' => 117,
    'title' => 'New payout request',
    'template' => '<p>[u.name] submitted a new payout request at [amount]</p>',
  ),
  115 => 
  array (
    'id' => 118,
    'title' => 'New offline payment request',
    'template' => '<p>[u.name] submitted a new offline payment request at [amount]</p>',
  ),
  116 => 
  array (
    'id' => 119,
    'title' => 'Content access approval',
    'template' => '<p>Your content access request approved. You can access all courses now...</p>',
  ),
  117 => 
  array (
    'id' => 120,
    'title' => 'Form submission by user',
    'template' => '<p>[u.name] submitted form [form_title]</p>',
  ),
  118 => 
  array (
    'id' => 121,
    'title' => 'Cart reminder',
    'template' => '<div>We\'re excited to invite you to complete your purchase with us! Enjoy exclusive benefits and offers by finalizing your order now.</div>',
  ),
  119 => 
  array (
    'id' => 122,
    'title' => 'Complete your purchase today with discount!',
    'template' => '<div>Here\'s an exclusive [discount_amount] discount coupon to encourage you to finalize your purchase with us. Discount Code : [discount_code]</div>',
  ),
  120 => 
  array (
    'id' => 123,
    'title' => 'Installment order canceled',
    'template' => '<p>Your installment order for [installment_title] has been canceled.</p>',
  ),
);

        foreach ($templates as $t) {
            DB::table('notification_templates')->updateOrInsert(
                ['id' => $t['id']],
                ['title' => $t['title'], 'template' => $t['template']]
            );
        }
    }
}
