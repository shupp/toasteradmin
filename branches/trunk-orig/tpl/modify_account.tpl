<?= _("Edit Account") ?><br>


<table border="0" cellpadding="15">
<tr><td class="border">
<form action="{php_self}" method="post">
<input type="hidden" name="module" value="Accounts">
<input type="hidden" name="domain" value="{domain}">
<input type="hidden" name="account" value="{account}">
<input type="hidden" name="event" value="modify_now">

<table border="0" cellspacing="0" cellpadding="3">
    <tr>
        <td class="title"><?= _('Modify User:') ?> {account}@{domain}</td>
    </tr>
    <tr>
        <td>
        <table border="0" width="100%">
        <tr>
            <td><?= _('Real Name') ?></td>
            <td><input type="text" name="comment" value="{comment}"></td>
        </tr>
        <tr>
            <td><?= _('Password') ?></td>
            <td><input type="password" name="password1" value=""></td>
        </tr>
        <tr>
            <td><?= _('Password (again)') ?></td>
            <td><input type="password" name="password2" value=""></td>
        </tr>
        </table>

        <hr>

        <table border="0" width="100%">
        <tr>
            <td><?= _('Routing:') ?></td>
            <td><input type="radio" name="routing" value="routing_standard" {routing_standard_checked}> <?= _('Standard (No Forwarding)') ?><br><hr></td>
        </tr>
        <tr>
            <td></td>
            <td><input type="radio" name="routing" value="routing_deleted" {routing_deleted_checked}> <?= _('All Mail Deleted') ?><br><hr></td>
        </tr>
        <tr>
            <td></td>
            <td><input type="radio" name="routing" value="routing_forwarded" {routing_forwarded_checked}>
            <?= _('Forward to:') ?> <input type="text" name="forward" value="{forward}"><br>
            <input type="checkbox" name="save_a_copy" {save_a_copy_checked}> <?= _('Save a Copy') ?>
            </td>
        </tr>
        </table>

        <hr>

        <input type="checkbox" name="vacation" {vacation_checked}> <?= _('Send a Vacation Auto-Response') ?><br>
        <?= _('Vacation Subject:') ?> <input type="text" name="vacation_subject" value="{vacation_subject}"><br>
        <?= _('Vacation Message:') ?><br>
        <textarea name="vacation_body" rows="10" cols="60">{vacation_body}</textarea>

        <hr>

        <center><input type="submit" value="<?= _('Modify Account') ?>"></center>
        </td>
    </tr>
</table>
</form>

</td></tr>
</table>
