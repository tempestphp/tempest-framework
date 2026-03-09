<html lang="en">
<head>
    <title>{{ $title }}</title>
</head>
<body>
    <div :if="$showHeader">
        <h1>{{ $heading }}</h1>
    </div>

    <ul>
        <li :foreach="$items as $item">{{ $item }}</li>
    </ul>

    <div :if="$isAdmin">
        <p>Admin panel</p>
    </div>
    <div :else>
        <p>Regular user</p>
    </div>
</body>
</html>
