<?php

require_once(__DIR__ . "/../bzion-load.php");

use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;

if (!DEVELOPMENT) {
    die("Populating the database with sample data isn't allowed in Production mode.\n");
}

$testPlayer = Player::getFromBZID(55976);
if ($testPlayer->isValid()) {
    die("Please clean your current data in the database, or you might end up with unwanted results.\n");
}


$db = Database::getInstance();

$alezakos   = Player::newPlayer(49434, "alezakos", 0, "active", Player::DEVELOPER, "", "Sample description");
$allejo     = Player::newPlayer(31098, "allejo", 0, "active", Player::DEVELOPER);
$ashvala    = Player::newPlayer(34353, "ashvala", 0, "active", Player::DEVELOPER);
$autoreport = Player::newPlayer(55976, "AutoReport");
$blast      = Player::newPlayer(180, "blast", 0, "active", Player::S_ADMIN);
$kierra     = Player::newPlayer(2229, "kierra", 0, "active", Player::ADMIN);
$mdskpr     = Player::newPlayer(8312, "mdskpr");
$snake      = Player::newPlayer(54497, "Snake12534");
$tw1sted    = Player::newPlayer(9736, "tw1sted", 0, "active", Player::DEVELOPER);

$olfm     = Team::createTeam("OpenLeague FM?", $kierra->getId(), "", "");
$reptiles = Team::createTeam("Reptitles", $snake->getId(), "", "");
$fflood   = Team::createTeam("Formal Flood", $allejo->getId(), "", "");
$lweak    = Team::createTeam("[LakeWeakness]", $mdskpr->getId(), "", "");
$gsepar   = Team::createTeam("Good Separation", $tw1sted->getId(), "", "");
$fradis   = Team::createTeam("Fractious disinclination", $ashvala->getId(), "", "");

$lweak->addMember($autoreport->getId());
$fflood->addMember($blast->getId());
$fradis->addMember($alezakos->getId());

Match::enterMatch($reptiles->getId(), $gsepar->getId(), 1, 9000, 17, $kierra->getId());
Match::enterMatch($olfm->getId(), $lweak->getId(), 0, 0, 20, $blast->getId());
Match::enterMatch($fflood->getId(), $lweak->getId(), 1, 15, 20, $autoreport->getId());
Match::enterMatch($gsepar->getId(), $fradis->getId(), 8, 23, 30, $kierra->getId());
Match::enterMatch($olfm->getId(), $lweak->getId(), 5, 4, 20, $kierra->getId());
Match::enterMatch($reptiles->getId(), $gsepar->getId(), 1, 1500, 20, $autoreport->getId());
Match::enterMatch($olfm->getId(), $lweak->getId(), 1, 1, 30, $autoreport->getId());
Match::enterMatch($fradis->getId(), $gsepar->getId(), 1, 2, 20, $kierra->getId());

$reptiles->update("activity", 9000, "i");
$fflood->update("activity", -18, "i");
$fradis->update("activity", 3.14159265358979323846, "d");

Server::addServer("BZPro Public HiX FFA", "bzpro.net:5154", $tw1sted->getId());
Server::addServer("BZPro Public HiX Rabbit Chase", "bzpro.net:5155", $tw1sted->getId());

$group_to = Group::createGroup("New blog", array(
    $alezakos->getId(),
    $allejo->getId(),
    $ashvala->getId(),
    $autoreport->getId(),
    $blast->getId(),
    $kierra->getId(),
    $mdskpr->getId(),
    $snake->getId(),
    $tw1sted->getId()
));
Message::sendMessage($group_to->getId(), $snake->getId(), "Check out my new blog!");

Ban::addBan($snake->getId(), $alezakos->getId(), "2014-09-15", "Snarke 12534 has been barned again", "Cuz you're snake", "256.512.104.1");
Ban::addBan($allejo->getId(), $tw1sted->getId(), "2014-05-17", "for using 'dope'", "dope", array("127.0.2.1", "128.0.3.2"));
Ban::addBan($tw1sted->getId(), $alezakos->getId(), "2014-06-12", "tw1sted banned for being too awesome");
Ban::addBan($alezakos->getId(), $tw1sted->getId(), "2014-11-01", "alezakos banned for breaking the build", "For breaking the build", array("256.512.124.1", "256.512.124.3"));

Page::addPage("Rules", "<p>This is a test page.</p>\n<p>Lets hope this works!</p>", $tw1sted->getId());
Page::addPage("Contact", "<p>If you find anything wrong, please stop by irc.freenode.net channel #sujevo and let a developer know.<br /><br />Thanks", $tw1sted->getId());

News::addNews("Announcement", "Very important Announcement", $mdskpr->getId());
News::addNews("Cats think we are bigger cats", "In order for your indess recognizes where this whole mistake has come, and why one accuses the pleasure and praise the pain, and I will open to you all and set apart, what those founders of the truth and, as builders of the happy life himself has said about it. No one, he says, despise, or hate, or flee the desire as such, but because great pain to follow, if you do not pursue pleasure rationally. Similarly, the pain was loved as such by no one or pursues or desires, but because occasionally circumstances occur that one means of toil and pain can procure him some great pleasure to look verschaften be. To stay here are a trivial, so none of us would ever undertakes laborious physical exercise, except to obtain some advantage from it. But who is probably the blame, which requires an appetite, has no annoying consequences, or one who avoids a pain, which shows no desire? In contrast, blames and you hate with the law, which can soften and seduced by the allurements of present pleasure, without seeing in his blind desire which pain and inconvenience wait his reason. Same debt meet Those who from weakness, i.e to escape the work and the pain, neglect their duties. A person can easily and quickly make the real difference, to a quiet time where the choice of the decision is completely free and nothing prevents them from doing what we like best, you have to grasp every pleasure and every pain avoided, but to times it hits in succession of duties or guilty of factual necessity that you reject the desire and complaints must not reject. Why then the way will make a selection so that it Achieve a greater rejection by a desire for it or by taking over some pains to spare larger.", $alezakos->getId());

echo "The database has been populated successfully.\n";

?>
