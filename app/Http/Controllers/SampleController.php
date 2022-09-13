<?php

namespace App\Http\Controllers;


use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use Hash;
use Illuminate\Routing\Redirector;
use Session;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SampleController extends Controller
{
    /**
     * @return Factory|View|Application
     */
    function index(): Factory|View|Application
    {
        return view('login');
    }

    /**
     * @return Factory|View|Application
     */
    function registration(): Factory|View|Application
    {
        return view('registration');
    }

    /**
     * @param Request $request
     * @return Redirector|Application|RedirectResponse
     */
    function validate_registration(Request $request): Redirector|Application|RedirectResponse
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        $data = $request->all();

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);

        return redirect('login')->with('success', 'Registration Completed, now you can login');
    }

    /**
     * @param Request $request
     * @return Redirector|Application|RedirectResponse
     */
    function validate_login(Request $request): Redirector|Application|RedirectResponse
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            return redirect('dashboard');
        }

        return redirect('login')->with('success', 'Login details are not valid');
    }

    /**
     * @return Factory|View|Redirector|RedirectResponse|Application
     */
    function dashboard(): Factory|View|Redirector|RedirectResponse|Application
    {
        if (Auth::check()) {
            return view('dashboard');
        }

        return redirect('login')->with('success', 'you are not allowed to access');
    }

    /**
     * @return Redirector|Application|RedirectResponse
     */
    function logout(): Redirector|Application|RedirectResponse
    {
        Session::flush();

        Auth::logout();

        return Redirect('login');
    }

    /**
     * @return Application|Factory|View|RedirectResponse|Redirector
     */
    public function profile()
    {
        if (Auth::check()) {
            $data = User::where('id', Auth::id())->get();

            return view('profile', compact('data'));
        }

        return redirect("login")->with('success', 'you are not allowed to access');
    }

    /**
     * @param Request $request
     * @return Application|RedirectResponse|Redirector
     */
    public function profile_validation(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'user_image' => 'image|mimes:jpg,png,jpeg|max:2048|dimensions:min_width=100,min_height=100,max_width=1000,max_height=1000'
        ]);

        $user_image = $request->hidden_user_image;

        if ($request->user_image != '') {
            $user_image = time() . '.' . $request->user_image->getClientOriginalExtension();

            $request->user_image->move(public_path('images'), $user_image);
        }

        $user = User::find(Auth::id());

        $user->name = $request->name;

        $user->email = $request->email;

        if ($request->password != '') {
            $user->password = Hash::make($request->password);
        }

        $user->user_image = $user_image;

        $user->save();

        return redirect('profile')->with('success', 'Profile Details Updated');
    }
}
