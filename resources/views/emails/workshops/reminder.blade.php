<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Workshop Reminder</title>
</head>
<body>
<p>Hello {{ $participant->name }},</p>

<p>This is a reminder for your workshop scheduled for tomorrow.</p>

<ul>
    <li><strong>Title:</strong> {{ $workshop->title }}</li>
    <li><strong>Description:</strong> {{ $workshop->description }}</li>
    <li><strong>Starts at:</strong> {{ $workshop->starts_at->format('Y-m-d H:i') }}</li>
    <li><strong>Ends at:</strong> {{ $workshop->ends_at->format('Y-m-d H:i') }}</li>
</ul>

<p>See you there.</p>
</body>
</html>
