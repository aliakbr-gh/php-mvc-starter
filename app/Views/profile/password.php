<div>
    <div class="page-heading">
        <div>
            <h1>Change Password</h1>
            <p>Update the password used to access your account.</p>
        </div>
        <a class="button button-secondary" href="<?= url('profile') ?>">Back to profile</a>
    </div>

    <div class="card narrow">
            <form method="post" action="<?= url('profile/password') ?>">
                <?= csrf_field() ?>

                <label>
                    Current password
                    <input
                        type="password"
                        name="current_password"
                        autocomplete="current-password"
                        required
                        autofocus
                    >
                </label>

                <label>
                    New password
                    <input
                        type="password"
                        name="new_password"
                        minlength="5"
                        autocomplete="new-password"
                        required
                    >
                </label>

                <label>
                    Confirm new password
                    <input
                        type="password"
                        name="confirm_password"
                        minlength="5"
                        autocomplete="new-password"
                        required
                    >
                </label>

                <div class="actions">
                    <button type="submit">Change password</button>
                    <a class="button button-secondary" href="<?= url('profile') ?>">Cancel</a>
                </div>
            </form>
    </div>
</div>
