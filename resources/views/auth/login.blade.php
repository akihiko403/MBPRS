@extends('layouts.app')

@php($loginPage = true)
@php($title = 'Login')

@section('content')
<div class="login-shell">
    <div class="login-card">
        <section class="login-art">
            <div aria-hidden="true" style="margin:0 auto 10px; width:118px; height:98px;">
                <svg viewBox="0 0 170 140" role="img" style="display:block; width:100%; height:100%;">
                    <rect x="56" y="24" width="7" height="70" fill="#4d4d4d" />
                    <rect x="49" y="31" width="7" height="63" fill="#616161" />
                    <path d="M54 29L106 6" stroke="#4d4d4d" stroke-width="5" stroke-linecap="round" />
                    <path d="M62 22L114 2" stroke="#616161" stroke-width="3.5" stroke-linecap="round" />
                    <path d="M70 18V96" stroke="#3f3f3f" stroke-width="6" stroke-linecap="round" />
                    <path d="M108 26V52" stroke="#3f3f3f" stroke-width="4" stroke-linecap="round" />
                    <path d="M108 52V66" stroke="#3f3f3f" stroke-width="3" stroke-linecap="round" />
                    <path d="M108 66L103 73" stroke="#3f3f3f" stroke-width="3.5" stroke-linecap="round" />
                    <path d="M108 66L113 73" stroke="#3f3f3f" stroke-width="3.5" stroke-linecap="round" />
                    <rect x="40" y="78" width="10" height="20" fill="#f7b500" />
                    <path d="M52 64L71 54L88 64V98H52Z" fill="#f7b500" />
                    <rect x="92" y="71" width="9" height="27" fill="#f7b500" />
                    <rect x="28" y="113" width="84" height="5" rx="2.5" fill="rgba(233,247,242,.55)" />
                </svg>
            </div>
            <h1 style="font-size:2rem; margin-top:0;">Municipal Building Permit Repository System</h1>
            <p style="line-height:1.8;">Securely manage building permit encoding, approvals, reporting, and records access in one municipal repository.</p>

        </section>
        <section class="login-form">
            <h2 style="margin-top:0;">Login</h2>
            <p class="muted">Enter your assigned username and password to continue.</p>
            <form action="{{ route('login.attempt') }}" method="POST" class="grid">
                @csrf
                <div>
                    <label for="username">Username</label>
                    <input id="username" name="username" value="{{ old('username') }}" required>
                </div>
                <div>
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <input style="width:auto;" id="remember" type="checkbox" name="remember" value="1">
                    <label for="remember" style="margin:0;">Keep me signed in</label>
                </div>
                <button class="btn" type="submit">Sign In</button>
            </form>
        </section>
    </div>
</div>
@endsection







