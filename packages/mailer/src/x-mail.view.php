from="{{ $from }}" to="{{ $to }}" subject="{{ $subject }}" async="{{ ($async ?? true) ? 'true' : 'false' }}" attachments="{{ implode(',', $attachments ?? []) }}"
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
</head>
<body>

<div :if="isset($pretext)" style="display: none;font-size: 1px;color:#fff;line-height: 1px;max-height: 0;opacity: 0;overflow: hidden;">{{ $pretext }}</div>

<x-slot/>

</body>
</html>
