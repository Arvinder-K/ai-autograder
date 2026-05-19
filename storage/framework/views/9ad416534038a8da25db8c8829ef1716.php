<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Sign In — AI Auto Grader</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Source+Sans+3:wght@300..700&display=swap"
        rel="stylesheet">

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <script>
        window.AppConfig = {
            baseUrl: <?php echo json_encode(rtrim(config('app.url'), '/'), 512) ?>,
        };
    </script>
</head>

<body class="antialiased">
    <?php echo e($slot); ?>

</body>

</html>
<?php /**PATH C:\xampp\htdocs\storyforge\storyforge\resources\views/components/layouts/auth.blade.php ENDPATH**/ ?>