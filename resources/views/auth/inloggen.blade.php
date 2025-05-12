@extends('layouts.pages')
@section('content')
<div class="py-[5rem] flex flex-col items-center">
    <h1 class="text-[#191919] text-[24px] font-light leading-[1.15]">Inloggen</h1>
    @if($errors->any())
        <div class="w-[300px] py-[0.4rem] rounded-sm text-sm bg-red-100 border-1 border-red-500 text-red-500 text-center mt-4">
            {{ $errors->first() }}
        </div>
    @endif
    <form method="POST" action="{{ route('inloggen.verwerk') }}" class="max-w-[400px] w-full bg-white p-[1.5rem] rounded-lg mt-4 flex flex-col gap-[1rem]">
        @csrf
        <div class="flex flex-col w-full">
            <label for="email" class="text-sm font-medium">E-mailadres</label>
            <input type="email" name="email" required class="w-full px-[0.75rem] py-[0.55rem] ring-1 ring-gray-200 focus:ring-[#ff64ba] outline-none rounded-md">
        </div>
        <div class="flex flex-col w-full">
            <label for="wachtwoord" class="text-sm font-medium">Wachtwoord</label>
            <input type="password" name="wachtwoord" required class="w-full px-[0.75rem] py-[0.55rem] ring-1 ring-gray-200 focus:ring-[#ff64ba] outline-none rounded-md">
        </div>
        <button type="submit" class="w-full mt-4 px-[1.5rem] py-[0.55rem] bg-[#ff64ba] hover:bg-[#96366c] transition rounded-md text-white text-[15px] font-medium">Inloggen</button>
    </form>
</div>
@endsection
