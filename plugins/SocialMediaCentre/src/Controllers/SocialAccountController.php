<?php

namespace Plugins\SocialMediaCentre\src\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Plugins\SocialMediaCentre\src\Models\SocialAccount;

class SocialAccountController extends Controller
{
    protected array $platforms = [
        'facebook' => 'Facebook Page',
        'instagram' => 'Instagram Business',
        'linkedin' => 'LinkedIn Page',
        'x' => 'X',
        'telegram' => 'Telegram',
        'whatsapp' => 'WhatsApp Channel',
    ];

    public function index()
    {
        $accounts = SocialAccount::latest()->paginate(15);
        return view('plugins.SocialMediaCentre::admin.accounts.index', ['accounts' => $accounts, 'platforms' => $this->platforms]);
    }

    public function create()
    {
        return view('plugins.SocialMediaCentre::admin.accounts.form', ['account' => new SocialAccount(['status' => 'manual']), 'platforms' => $this->platforms]);
    }

    public function store(Request $request)
    {
        SocialAccount::create($this->data($request));
        return redirect()->route('admin.social.accounts.index')->with('success', 'Social account added.');
    }

    public function edit(SocialAccount $account)
    {
        return view('plugins.SocialMediaCentre::admin.accounts.form', compact('account') + ['platforms' => $this->platforms]);
    }

    public function update(Request $request, SocialAccount $account)
    {
        $account->update($this->data($request));
        return redirect()->route('admin.social.accounts.index')->with('success', 'Social account updated.');
    }

    public function destroy(SocialAccount $account)
    {
        $account->delete();
        return back()->with('success', 'Social account removed.');
    }

    protected function data(Request $request): array
    {
        return $request->validate([
            'platform' => 'required|in:' . implode(',', array_keys($this->platforms)),
            'name' => 'required|string|max:255',
            'handle' => 'nullable|string|max:255',
            'status' => 'required|in:manual,connected,disabled',
        ]);
    }
}
