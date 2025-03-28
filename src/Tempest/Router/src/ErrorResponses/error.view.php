<html lang="en">
<head>
  <title>{{ $title }}</title>
  <style>{!! $css !!}</style>
</head>
<body>
  <div class="flex flex-col justify-center items-center bg-[#061324] min-w-screen min-h-screen text-[#a8caf7] antialiased">
    <div class="px-8 container">
      <h1 class="font-thin text-8xl flex gap-x-1">
				<span class="text-[#4c6586]">HTTP</span>
				<span>{{ $status }}</span>
			</h1>
      <p :if="$message" class="text-xl uppercase text-[#4c6586]">{{ \Tempest\Support\Str\ensure_ends_with($message, '.') }}</p>
    </div>
  </div>
</body>
</html>
