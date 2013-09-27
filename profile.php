<?php

include("bzion-load.php");

$header = new Header();

if (!isset($_SESSION['username'])) {
    $header->go("home");
}

$me = new Player($_SESSION['bzid']);

if (isset($_POST['submit'])) {
    echo "<pre style='margin-top: 50px'>";
    print_r($_POST);
    echo "</pre>";
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
}

if (isset($action) && ($action == "edit")) {
    $header->draw("Profile :: Edit");
    ?>

    <h3>Edit Profile</h3>
    <form action="edit" method="post">
        Avatar: <input type="text" name="avatar" value="<?php echo $me->getAvatar(); ?>" size="100"><br />
        Country: <select name="country">
            <?php
            $countries = Country::getCountries();
            foreach ($countries as $key => $value) {
                $country = new Country($value);
                ?>
                <option value="<?php echo $country->getId(); ?>"><?php echo $country->getName(); ?></option>
                <?php
            }
            ?>
        </select><br />
        Timezone: <select name="timezone">
            <?php
            for ($i=-12; $i <= 12; $i++) { 
                if ($i >= 0) $plus = "+";
                echo "<option value='$i'>GMT $plus$i</option>";
            }
            ?>
        </select><br />
        Profile comments:<br /><textarea value="description" rows="5" cols="40"><?php echo $me->getDescription(); ?></textarea><br />
        <input type="submit" name="submit" value="Update Profile">
    </form>
    
    <?php
} else {
    $header->draw("Profile");

    $playerTeam = $me->getTeam();
    $teamlink = $playerTeam->getName();

    if ($playerTeam->isValid()) {
        $teamlink = '<a href="' . $playerTeam->getURL() . '">' . $teamlink . '</a>';
    }

    echo "<h2>" . $me->getUsername() . "</h2><br />";
    echo "Team: $teamlink<br />";
    echo "Joined: " . $me->getJoinedDate() . "<br />";

    echo "<br />More content coming soon...<br />";

    echo "<br /><a href='profile/edit'>Edit your profile...</a>";

}

$footer = new Footer();
$footer->draw();

?>
