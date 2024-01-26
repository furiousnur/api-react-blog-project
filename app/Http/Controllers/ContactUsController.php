<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactUsController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required',
//            'email' => 'required|email:rfc,dns',
            'message' => 'required|string|max:255',
        ]);

        Contact::create($request->all());

        return response()->json([
            'message' => 'Thank you for your message. We will get back to you soon.',
        ]);
    }
}
