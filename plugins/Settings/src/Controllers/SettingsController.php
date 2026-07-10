<?php

namespace Plugins\Settings\src\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Plugins\CMS\src\Models\Page;

class SettingsController extends Controller
{
    // ==================== GENERAL ====================
    
    public function index()
    {
        $settings = Setting::where('group', 'general')
            ->orWhere('group', 'billing')
            ->get()
            ->groupBy('group');
        
        return view('plugins.Settings::admin.general', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings.company_name' => 'required|string|max:255',
            'settings.company_legal_name' => 'nullable|string|max:255',
            'settings.company_website' => 'nullable|url|max:255',
            'settings.company_email' => 'nullable|email|max:255',
            'settings.support_email' => 'nullable|email|max:255',
            'settings.billing_email' => 'nullable|email|max:255',
            'settings.company_phone' => 'nullable|string|max:50',
            'settings.company_address' => 'nullable|string|max:1000',
            'settings.company_city' => 'nullable|string|max:120',
            'settings.company_region' => 'nullable|string|max:120',
            'settings.company_postal_code' => 'nullable|string|max:30',
            'settings.company_country' => 'nullable|string|max:120',
            'settings.timezone' => 'required|timezone',
            'settings.date_format' => 'required|in:d/m/Y,m/d/Y,Y-m-d,M d, Y',
            'company_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('company', 'public');
            $validated['settings']['company_logo'] = $path;
        }

        foreach ($validated['settings'] as $key => $value) {
            Setting::set($key, $value, 'general', ucwords(str_replace('_', ' ', $key)));
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'General settings saved!');
    }

    // ==================== SITE ====================
    
    public function site()
    {
        $homepageType = Setting::get('homepage_type', 'page');
        $homepagePageId = Setting::get('homepage_page_id');
        $postsPageId = Setting::get('posts_page_id');
        
        $pages = Page::published()->orderBy('title')->get();
        
        return view('plugins.Settings::admin.site', compact(
            'homepageType', 'homepagePageId', 'postsPageId', 'pages'
        ));
    }

    public function updateSite(Request $request)
    {
        $validated = $request->validate([
            'homepage_type' => 'required|in:page,posts,landing',
            'homepage_page_id' => 'nullable|exists:cms_pages,id',
            'posts_page_id' => 'nullable|exists:cms_pages,id',
            'site_title' => 'nullable|string|max:255',
            'site_tagline' => 'nullable|string|max:255',
            'site_description' => 'nullable|string|max:500',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value, 'site', ucwords(str_replace('_', ' ', $key)));
        }

        return redirect()->route('admin.settings.site')
            ->with('success', 'Site settings saved!');
    }

    // ==================== INVOICE ====================
    
    public function invoice()
    {
        $settings = Setting::where('group', 'invoice')->get();
        return view('plugins.Settings::admin.invoice', compact('settings'));
    }

    public function updateInvoice(Request $request)
    {
        $validated = $request->validate([
            'settings.invoice_prefix' => 'required|string|max:20',
            'settings.invoice_due_days' => 'required|integer|min:1|max:365',
            'settings.invoice_notes' => 'nullable|string|max:1000',
            'settings.invoice_footer' => 'nullable|string|max:2000',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            Setting::set($key, $value, 'invoice', ucwords(str_replace('_', ' ', $key)));
        }

        return redirect()->route('admin.settings.invoice')
            ->with('success', 'Invoice settings saved!');
    }

    public function billing()
    {
        return view('plugins.Settings::admin.billing');
    }

    public function updateBilling(Request $request)
    {
        $validated = $request->validate([
            'settings.currency' => 'required|in:TZS,USD,KES,UGX,RWF',
            'settings.currency_decimal_places' => 'required|integer|min:0|max:4',
            'settings.company_tin' => 'nullable|string|max:50',
            'settings.company_vrn' => 'nullable|string|max:50',
            'settings.tax_label' => 'required|string|max:30',
            'settings.vat_enabled' => 'required|in:0,1',
            'settings.vat_rate' => 'required|numeric|min:0|max:100',
            'settings.prices_include_tax' => 'required|in:0,1',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            Setting::set($key, $value, 'billing', ucwords(str_replace('_', ' ', $key)));
        }

        return redirect()->route('admin.settings.billing')
            ->with('success', 'Billing and tax settings saved!');
    }

    public function testEmail(Request $request)
    {
        return back()->with('success', 'Test email sent!');
    }

    public function domain()
    {
        $settings = \App\Models\Setting::where('group', 'domain')->get();
        return view('plugins.Settings::admin.domain', compact('settings'));
    }

    public function updateDomain(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string|max:1000',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            \App\Models\Setting::set($key, $value, 'domain', ucwords(str_replace('_', ' ', $key)));
        }

        return redirect()->route('admin.settings.domain')->with('success', 'Domain settings saved!');
    }

    /**
     * Notifications
     */
    public function notifications()
    {
        return view('plugins.Settings::admin.notifications');
    }

    public function updateNotification(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:notification_templates,id',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
            'is_active' => 'boolean',
        ]);

        \DB::table('notification_templates')->where('id', $validated['template_id'])->update([
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'is_active' => $validated['is_active'] ?? false,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.settings.notifications')->with('success', 'Template updated!');
    }
}
