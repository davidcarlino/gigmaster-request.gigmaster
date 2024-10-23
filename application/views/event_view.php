<div class="request">
    <div class="content">
        <?php if (isset($error)): ?>
            <div class="error-message" style="color: red; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php elseif (isset($event)): ?>
            <h1><?php echo htmlspecialchars($event->name); ?></h1> <!-- Ensure event name is safe for output -->
        <?php else: ?>
            <h1>No event information available.</h1> <!-- Fallback message if neither error nor event is set -->
        <?php endif; ?>
    </div>
</div>