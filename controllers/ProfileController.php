<?php

class ProfileController extends HTMLController {
    private $me;

    public function setup() {
        $header = new Header();

        $this->me = new Player($_SESSION['playerId']);

        if (!isset($_SESSION['username'])) {
            $header->go("home");
        }
    }

    public function cleanup() {
        $footer = new Footer();

        $footer->addScript("assets/js/profile.js");
        $footer->draw();
    }

    public function editAction() {
        $this->drawHeader("Profile :: Edit");
        ?>

        <h3>Edit Profile</h3>
        <form>
            Avatar: <input type="text" name="avatar" value="<?php echo $this->me->getAvatar(); ?>" size="100" class="profile_avatar"><br />
            Country: <select name="country" class="profile_country">
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
            Timezone: <select name="timezone" class="profile_timezone">
                <?php
                for ($i=-12; $i <= 12; $i++) {
                    $plus = ($i >= 0) ? "+" : "";
                    echo "<option value='$i'>GMT $plus$i</option>";
                }
                ?>
            </select><br />
            Profile comments:<br /><textarea value="description" rows="5" cols="40" name="description" class="profile_description"><?php echo $this->me->getDescription(); ?></textarea><br />
            Theme: <select class="themes">
                <option>Industrial</option>
                <option>Colorful</option>
            </select><br />
            <button onclick="updateProfile()" type="button" class="ladda-button" data-style="zoom-out"><span class="ladda-label">Update</span></button>
        </form>

        <?php
    }

    public function showAction() {
        $this->drawHeader("Profile");

        $playerTeam = $this->me->getTeam();
        $teamlink = $playerTeam->getName();

        if ($playerTeam->isValid()) {
            $teamlink = '<a href="' . $playerTeam->getURL() . '">' . $teamlink . '</a>';
        }

        echo "<h2>" . $this->me->getUsername() . "</h2><br />";
        echo "Team: $teamlink<br />";
        echo "Joined: " . $this->me->getJoinedDate() . "<br />";

        echo "<br />", $this->me->getDescription() ,"<br />";

        echo "<br /><a href='" . $this->generate("profile_edit") ."'>Edit your profile...</a>";

    }
}
