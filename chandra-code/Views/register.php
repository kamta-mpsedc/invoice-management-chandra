<form method="post" action="<?php echo base_url('register') ?>">
    <input type="text" name="name" placeholder="Name"><br><br>
       <br>
    <span style="color:red">
        <?php echo isset($validation) ? $validation->getError('name') : '' ?>
    </span>
    <br><br>
    <input type="email" name="email" placeholder="Email"><br><br>
     <span style="color:red">
        <?php echo isset($validation) ? $validation->getError('email') : '' ?>
    </span>
    <br><br>

    <input type="password" name="password" placeholder="Password"><br><br>
<span style="color:red">
        <?php echo isset($validation) ? $validation->getError('password') : '' ?>
    </span>
    <br><br>
    <select name="role">
        <option value="User">User</option>
        <option value="Admin">Admin</option>
    </select><br><br>
 <span style="color:red">
        <?php echo isset($validation) ? $validation->getError('role') : '' ?>
    </span>
    <br><br>
    <button type="submit">Register</button>
</form>