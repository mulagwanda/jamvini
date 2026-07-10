<?php

namespace Plugins\Forms\src\Controllers;

use App\Http\Controllers\Controller;
use Plugins\Forms\src\Models\Form;
use Plugins\Forms\src\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormController extends Controller
{
    public function index()
    {
        $forms = Form::withCount('submissions')->latest()->paginate(15);
        return view('plugins.Forms::admin.index', compact('forms'));
    }

    public function create()
    {
        return view('plugins.Forms::admin.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'recipient_email' => 'nullable|email',
            'success_message' => 'nullable|string',
            'fields' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['fields'] = json_decode($validated['fields'] ?? '[]', true);

        Form::create($validated);

        return redirect()->route('admin.forms.index')->with('success', 'Form created!');
    }

    public function edit(Form $form)
    {
        return view('plugins.Forms::admin.edit', compact('form'));
    }

    public function update(Request $request, Form $form)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'recipient_email' => 'nullable|email',
            'success_message' => 'nullable|string',
            'is_active' => 'boolean',
            'fields' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['fields'] = json_decode($validated['fields'] ?? '[]', true);

        $form->update($validated);

        return redirect()->route('admin.forms.index')->with('success', 'Form updated!');
    }

    public function destroy(Form $form)
    {
        $form->delete();
        return redirect()->route('admin.forms.index')->with('success', 'Form deleted!');
    }

    public function submissions(Form $form)
    {
        $submissions = $form->submissions()->latest()->paginate(20);
        return view('plugins.Forms::admin.submissions', compact('form', 'submissions'));
    }

    public function showSubmission(Form $form, Submission $submission)
    {
        $submission->update(['status' => 'read']);
        return view('plugins.Forms::admin.submission-show', compact('form', 'submission'));
    }

    public function deleteSubmission(Form $form, Submission $submission)
    {
        $submission->delete();
        return redirect()->route('admin.forms.submissions', $form)->with('success', 'Submission deleted!');
    }

    // Public render
    public function render(string $slug)
    {
        $form = Form::where('slug', $slug)->where('is_active', true)->firstOrFail();
        return view('plugins.Forms::render', compact('form'));
    }

    public function submit(Request $request, string $slug)
    {
        $form = Form::where('slug', $slug)->where('is_active', true)->firstOrFail();
        
        $rules = [];
        foreach ($form->fields ?? [] as $field) {
            if (!empty($field['required'])) {
                $rules[$field['name']] = 'required';
            }
        }

        $data = $request->validate($rules);

        $form->submissions()->create([
            'data' => $data,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('form_success', $form->success_message);
    }
}