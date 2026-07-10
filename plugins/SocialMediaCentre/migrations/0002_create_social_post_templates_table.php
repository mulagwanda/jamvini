<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_post_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->default('general');
            $table->string('description')->nullable();
            $table->string('title_template');
            $table->longText('caption_template');
            $table->json('hashtags')->nullable();
            $table->json('platforms')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_system')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        $templates = [
            [
                'name' => 'Hosting Promo',
                'category' => 'offers',
                'description' => 'Promote a shared, VPS, or business hosting package.',
                'title_template' => '{{package_name}} Hosting Offer',
                'caption_template' => "Need faster, safer hosting for your website?\n\nGet {{package_name}} from {{price}}/{{billing_cycle}} with {{key_benefit}}.\n\nPerfect for {{target_customer}}.\n\nVisit {{website_url}} or contact us today.",
                'hashtags' => ['#webhosting', '#hosting', '#businesswebsites'],
                'platforms' => ['facebook', 'instagram', 'linkedin', 'whatsapp'],
            ],
            [
                'name' => 'Domain Offer',
                'category' => 'offers',
                'description' => 'Advertise domain registration or transfer offers.',
                'title_template' => '{{tld}} Domain Offer',
                'caption_template' => "Your brand deserves a professional domain name.\n\nRegister {{tld}} domains from {{price}} and secure your online identity today.\n\nSearch your domain at {{website_url}}.",
                'hashtags' => ['#domains', '#webhosting', '#onlinebusiness'],
                'platforms' => ['facebook', 'instagram', 'x', 'whatsapp'],
            ],
            [
                'name' => 'SSL Reminder',
                'category' => 'education',
                'description' => 'Educate customers about SSL certificates.',
                'title_template' => 'Protect Your Website With SSL',
                'caption_template' => "A secure website builds trust.\n\nSSL protects customer data, improves confidence, and helps your visitors know they are in the right place.\n\nAsk us to activate SSL for your website today.",
                'hashtags' => ['#ssl', '#websecurity', '#hosting'],
                'platforms' => ['facebook', 'linkedin', 'whatsapp'],
            ],
            [
                'name' => 'Maintenance Notice',
                'category' => 'announcements',
                'description' => 'Notify customers about planned maintenance.',
                'title_template' => 'Scheduled Maintenance Notice',
                'caption_template' => "Scheduled maintenance notice\n\nService: {{service_name}}\nDate: {{maintenance_date}}\nTime: {{maintenance_time}}\nExpected impact: {{impact_summary}}\n\nWe appreciate your patience as we improve our services.",
                'hashtags' => ['#maintenance', '#serviceupdate'],
                'platforms' => ['facebook', 'telegram', 'whatsapp'],
            ],
            [
                'name' => 'New Package Launch',
                'category' => 'announcements',
                'description' => 'Announce a new hosting package or product.',
                'title_template' => 'New Package: {{package_name}}',
                'caption_template' => "We have launched {{package_name}}.\n\nBuilt for {{target_customer}}, this package includes {{feature_one}}, {{feature_two}}, and {{feature_three}}.\n\nExplore it at {{website_url}}.",
                'hashtags' => ['#newlaunch', '#hosting', '#websolutions'],
                'platforms' => ['facebook', 'instagram', 'linkedin', 'x'],
            ],
            [
                'name' => 'Customer Education Tip',
                'category' => 'education',
                'description' => 'Share practical hosting or website advice.',
                'title_template' => 'Website Tip: {{tip_topic}}',
                'caption_template' => "Website tip: {{tip_topic}}\n\n{{tip_body}}\n\nSmall improvements can make your website faster, safer, and easier for customers to trust.",
                'hashtags' => ['#websitetips', '#hosting', '#digitalbusiness'],
                'platforms' => ['facebook', 'linkedin', 'x'],
            ],
            [
                'name' => 'Holiday Greeting',
                'category' => 'community',
                'description' => 'Send seasonal greetings from the company.',
                'title_template' => '{{holiday_name}} Greetings',
                'caption_template' => "Happy {{holiday_name}} from {{company_name}}.\n\nWe wish you, your team, and your family a peaceful and successful season.\n\nThank you for trusting us with your online presence.",
                'hashtags' => ['#greetings', '#community'],
                'platforms' => ['facebook', 'instagram', 'whatsapp'],
            ],
            [
                'name' => 'Payment Reminder',
                'category' => 'customer-care',
                'description' => 'A gentle public reminder about renewals.',
                'title_template' => 'Service Renewal Reminder',
                'caption_template' => "Reminder: keep your services active by renewing before the due date.\n\nTimely renewal helps avoid website, email, or domain interruption.\n\nLog in to your client area or contact support if you need help.",
                'hashtags' => ['#customercare', '#hosting'],
                'platforms' => ['facebook', 'telegram', 'whatsapp'],
            ],
        ];

        foreach ($templates as $index => $template) {
            DB::table('social_post_templates')->insert([
                'name' => $template['name'],
                'slug' => Str::slug($template['name']),
                'category' => $template['category'],
                'description' => $template['description'],
                'title_template' => $template['title_template'],
                'caption_template' => $template['caption_template'],
                'hashtags' => json_encode($template['hashtags']),
                'platforms' => json_encode($template['platforms']),
                'status' => 'active',
                'is_system' => true,
                'sort_order' => $index + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('social_post_templates');
    }
};
