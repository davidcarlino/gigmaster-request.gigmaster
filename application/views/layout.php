<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url('public/css/style.css'); ?>">
    <script>
        function redirectToSongs() {
            const code = "<?php echo htmlspecialchars($this->input->get('code')); ?>"; // Get the event code
            window.location.href = "<?php echo base_url('songs'); ?>" + (code ? "?code=" + encodeURIComponent(code) : "");
        }
    </script>
</head>
<body>
    <header>
        <a href="<?php echo base_url(); ?>"><img src="<?php echo base_url('public/img/logo-dark.png'); ?>" alt="Loading..." class="logo"></a>
    </header>
    <form>     
        <div class="container">
            <?php echo $content; ?>
        </div>
        <?php
        if (count($footerBtn) > 0):
        ?>
            <footer>
                <?php echo implode('', array_map(function($btn, $id) {
                    return "<button type='button' id='{$id}' class=\"black-btn\">{$btn}</button>";
                }, $footerBtn, array_keys($footerBtn))); ?>
            </footer>
        <?php
        endif;
        ?>
    </form>
    <script src="<?php echo base_url('public/js/script.js'); ?>"></script>
</body>
