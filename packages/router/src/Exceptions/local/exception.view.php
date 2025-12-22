<!DOCTYPE html>
<html lang="en" class="flex flex-col">
	<head>
		<meta charset="UTF-8" />
		<link rel="icon" type="image/svg+xml" href="/vite.svg" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Debug</title>
		<style>
			{!! $css !!}
		</style>
	</head>
	<body class="flex flex-col grow">
		<script id="tempest-hydration" type="application/json">
			{!! $hydration !!}
		</script>
		<div id="root" class="flex flex-col grow"></div>
		<script type="module">
			{!! $script !!}
		</script>
	</body>
</html>
