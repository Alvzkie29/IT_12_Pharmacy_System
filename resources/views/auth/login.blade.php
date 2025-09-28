
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gradient-to-br from-green-400 to-green-700">
  <div class="bg-white/90 p-8 rounded-2xl shadow-xl w-96">
    <div class="flex justify-center mb-4">
      <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-20">
    </div>
    <h2 class="text-2xl font-bold text-center text-green-700 mb-6">Login</h2>



        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block font-medium">Username</label>
                <input type="username" name="username" class="w-full border rounded p-2" required>
            </div>
            <div>
                <label class="block font-medium">Password</label>
                <input type="password" name="password" class="w-full border rounded p-2" required>
            </div>
            <button type="submit" class="w-full bg-green-600 hover:bg-blue-700 text-white py-2 rounded">
                Login
            </button>
        </form>
    </div>
</body>
</html>
