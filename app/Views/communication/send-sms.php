<div>
    <div class="page-heading">
        <div>
            <h1>Send SMS</h1>
            <p>Open WhatsApp with a receiver number and message prefilled.</p>
        </div>
    </div>

    <section class="card narrow">
        <h2>WhatsApp message</h2>
        <p>You will confirm the final send after WhatsApp opens.</p>
        <form method="post" action="<?= url('send-sms') ?>">
            <?= csrf_field() ?>
            <label>
                Receiver number
                <input type="tel" name="receiver_number" maxlength="24" placeholder="923001234567" required>
                <small>Use international format with country code.</small>
            </label>
            <label>
                Message
                <textarea name="message" rows="7" maxlength="4096" required></textarea>
            </label>
            <?php if (hasPermission('sms.send')): ?>
                <div class="actions">
                    <button type="submit">Continue to WhatsApp</button>
                </div>
            <?php endif; ?>
        </form>
    </section>
</div>
