#!/usr/bin/env php
<?php

use BZIon\Event\ConversationAbandonEvent;
use BZIon\Event\ConversationJoinEvent;
use BZIon\Event\ConversationKickEvent;
use BZIon\Event\ConversationRenameEvent;
use BZIon\Event\Events;
use BZIon\Event\WelcomeEvent;

require_once __DIR__ . "/../bzion-load.php";

$kernel = new AppKernel("dev", true);
$kernel->boot();

$testPlayer = Player::getFromBZID(3030);
if ($testPlayer->isValid()) {
    die("Please clear your current data in the database or you'll end up with duplicate entries.\n");
}

echo "Adding players...";
$alezakos   = Player::newPlayer(49434, "alezakos", null, "active", Player::DEVELOPER, "", "Sample description", 84);
$allejo     = Player::newPlayer(31098, "allejo", null, "active", Player::DEVELOPER, "", "I'm the one who breaks the build", 227);
$ashvala    = Player::newPlayer(34353, "ashvala", null, "active", Player::DEVELOPER, "", "", 100);
$autoreport = Player::newPlayer(55976, "AutoReport", null, "test");
$blast      = Player::newPlayer(180, "blast", null, "active", Player::S_ADMIN);
$kierra     = Player::newPlayer(2229, "kierra", null, "active", Player::ADMIN, "", "", 174);
$mdskpr     = Player::newPlayer(8312, "mdskpr");
$snake      = Player::newPlayer(54497, "Snake12534");
$tw1sted    = Player::newPlayer(9736, "tw1sted", null, "active", Player::DEVELOPER);
$brad       = Player::newPlayer(3030, "brad", null, "active", Player::S_ADMIN, "", "I keep nagging about when this project will be done");
$constitution = Player::newPlayer(9972, "Constitution", null, "active", Player::S_ADMIN);

$oldSnake = Player::newPlayer(54498, "Snake12534");
$oldSnake->setOutdated(true);

$allPlayers = array(
    $alezakos,
    $allejo,
    $ashvala,
    $autoreport,
    $blast,
    $kierra,
    $mdskpr,
    $snake,
    $tw1sted,
    $brad,
    $constitution);
echo " done!";

echo "\nSending notifications...";
foreach (Player::getPlayers() as $player) {
    $event = new WelcomeEvent('Welcome to ' . Service::getParameter('bzion.site.name') . '!', $player);
    Notification::newNotification($player->getId(), 'welcome', $event);
}
echo " done!";

echo "\nAdding deleted objects...";
Team::createTeam("Amphibians", $snake->getId(), "", "")->delete();
$snake->refresh();
Team::createTeam("Serpents", $snake->getId(), "", "")->delete();
$snake->refresh();
Page::addPage("Test", "<p>This is a deleted page</p>", $tw1sted->getId())->delete();
echo " done!";

echo "\nAdding teams...";
$olfm      = Team::createTeam("OpenLeague FM?", $kierra->getId(), "", "");
$reptitles = Team::createTeam("Reptitles", $snake->getId(), "", "", "open");
$fflood    = Team::createTeam("Formal Flood", $allejo->getId(), "", "");
$lweak     = Team::createTeam("[LakeWeakness]", $mdskpr->getId(), "", "");
$gsepar    = Team::createTeam("Good Separation", $tw1sted->getId(), "", "");
$gsepar->changeElo('100');
$fradis    = Team::createTeam("Fractious disinclination", $ashvala->getId(), "", "");
echo " done!";

echo "\nAdding members to teams...";
$lweak->addMember($autoreport->getId());
$fflood->addMember($blast->getId());
$fradis->addMember($alezakos->getId());
$reptitles->addMember($brad->getId());
echo " done!";

echo "\nAdding maps...";
$six = Map::addMap("Six", "six", "six is the best map ever");
$monocati = Map::addMap("Monocati", "monocati", "a single map");
$toast = Map::addMap("Toast", null, <<<TOAST
Toast is a very old map with **deterministic** outcome. A *true* map for *true* masters.

#### Choice
Toast has been the **map of choice** for many of the league's players and teams, including
but not limited to LakeWeakness, Fractious Disinclination and Former Gratification. The map
has been providing inspiration and will be providing inspiration for years to come, sparking
interesting matches, clever matching techniques, and even spin-offs of a similar style.
However, match goers have remained attached to toast's simple yet powerful layout, taking
advantage of all the improbable possibilities, bringing enjoyment to all kinds of BZFlag
players around the globe.
TOAST
);

echo "\nAdding matches...";
Match::enterMatch($reptitles->getId(), $gsepar->getId(), 1, 9000, 17, $kierra->getId(), "now", [], [], null, null, null, $six->getId());
Match::enterMatch($olfm->getId(), $lweak->getId(), 0, 0, 20, $blast->getId(), "now", [], [], null, null, null, $toast->getId());
Match::enterMatch($fflood->getId(), $lweak->getId(), 1, 15, 20, $autoreport->getId(), "now", [], [], null, null, null, $six->getId());
Match::enterMatch($gsepar->getId(), $fradis->getId(), 8, 23, 30, $kierra->getId(), "now", [], [], null, null, null, $toast->getId());
Match::enterMatch($olfm->getId(), $lweak->getId(), 5, 4, 20, $kierra->getId());
Match::enterMatch($reptitles->getId(), $gsepar->getId(), 1, 1500, 20, $autoreport->getId(), "now", [], [], null, null, null, $monocati->getId());
Match::enterMatch($olfm->getId(), $lweak->getId(), 1, 1, 30, $autoreport->getId(), "now", [], [], null, null, null, $monocati->getId());
Match::enterMatch($fradis->getId(), $gsepar->getId(), 1, 2, 20, $kierra->getId(), "now", [], [], null, null, null, $monocati->getId());
echo " done!";

echo "\nUpdating teams...";
$reptitles->update("activity", 9000);
$fflood->update("activity", -18);
$fradis->update("activity", 3.14159265358979323846);
echo " done!";

echo "\nAdding servers...";
Server::addServer("Wingfights Fountains", "helit.tech", 5154, 151, $alezakos->getId());
Server::addServer("BZPro Public HiX Rabbit Chase", "bzpro.net", 5155, 227, $tw1sted->getId());
echo " done!";

echo "\nAdding messages...";
$conversation = Conversation::createConversation("New blog", $snake->getId(), $allPlayers);

for ($i = 0; $i <= 10; ++$i) {
    Conversation::createConversation("Extra test $i", $alezakos->getId(), $allPlayers)->sendMessage($allejo, "This is a test conversation");
}

$event = new ConversationRenameEvent($conversation, "New message", "New blorg", $snake);
ConversationEvent::storeEvent($conversation->getId(), $event, Events::CONVERSATION_RENAME);
$event = new ConversationRenameEvent($conversation, "New blorg", "New blog", $snake);
ConversationEvent::storeEvent($conversation->getId(), $event, Events::CONVERSATION_RENAME);
$conversation->sendMessage($snake, "Check out my new blog!");

$conversation = Conversation::createConversation("Serious discussion", $constitution->getId(), $allPlayers);
$conversation->sendMessage($ashvala, "hey");
$conversation->sendMessage($alezakos, "hm, what different machine learning some reason why I turned on the cloud version");
$conversation->sendMessage($allejo, "then I can type at 12,000 wpm?");
$conversation->sendMessage($blast, "like:");
$conversation->sendMessage($allejo, "tea?");
$conversation->sendMessage($allejo, "what would have some.");
$conversation->sendMessage($snake, "hahaha I'm sure I've used a url shorten urls book I'm not sure it would be used it");
$conversation->sendMessage($blast, "so I assume you're the correlative still always_ goes down history\"'s for anything");
$conversation->sendMessage($kierra, "I had a page. No addon, plugin to windows it...");
$conversation->sendMessage($allejo, "so I'm the world  (which was amazing :)");
$conversation->sendMessage($brad, "she also going to essential command and to https://github Releases?\"  \"No!\"  \"STOP! IT!\"  \"*pbbbttt*\"  \"That's interesting I want help");
$conversation->sendMessage($brad, "I wonder 600KB) screenshot here. but how much I know all I was doing to debug, visual studio");
$conversation->sendMessage($brad, "and night night!");
$conversation->sendMessage($constitution, "okay??\" \"for sandalism on wikipedia?");
$event = new ConversationJoinEvent($conversation, array($autoreport));
ConversationEvent::storeEvent($conversation->getId(), $event, Events::CONVERSATION_JOIN);
$conversation->sendMessage($blast, "and the migrate-database, usually is reversed");
$conversation->sendMessage($alezakos, "uhm");
$conversation->sendMessage($blast, "lol");
$conversation->sendMessage($alezakos, "That makes a look at the kai responsibility issue: https://twig.sensiolabs.org/doc/function closer than that's an x-wing the default search engineering can use to acknowledge");
$conversation->sendMessage($constitution, "Makes sense.");
$conversation->sendMessage($autoreport, "yes, it counts as pseudocode.");
$conversation->sendMessage($autoreport, "does a bot have consequency trading?");
$conversation->sendMessage($allejo, "autoreport: Without sound effect (and then connection)");
$conversation->sendMessage($snake, "allejo");
$conversation->sendMessage($brad, "what's your self-interest?");
$conversation->sendMessage($autoreport, "none, but I have a hammer");
$conversation->sendMessage($autoreport, "that's way beyond, but the mouse is with me, snake?");
$conversation->sendMessage($snake, "that's red to sleep. ZZZzzzâ€¦)");
$conversation->sendMessage($autoreport, "okay, that was never look up \"github has a web programs\", but hey, I never look up to see if restart bzflag are general use.");
$conversation->sendMessage($autoreport, "that's left?");
$conversation->sendMessage($autoreport, "assuming language in a new ADD message to an interesting pads for cell phones");
$conversation->sendMessage($autoreport, "and not in sausage");
$event = new ConversationAbandonEvent($conversation, $autoreport);
ConversationEvent::storeEvent($conversation->getId(), $event, Events::CONVERSATION_ABANDON);
$conversation->sendMessage($autoreport, "hey, I never release is the first join properly making on a PC where was over 2 hours until you read there, and instead of globally affirming with UTF8 character, and in here the printers or are them :P");
$conversation->sendMessage($autoreport, ":)");

$conversation = Conversation::createConversation("½ ¼ ¾ ⅓ ⅔ ÷ ± ∞ π", $ashvala->getId(), $allPlayers);
$conversation->sendMessage($autoreport, "this is a test message to the conversation");
$conversation->sendMessage($autoreport, "this is another test message to the conversation");
$conversation->sendMessage($autoreport, "this is yet another test message to the conversation");
$conversation->sendMessage($autoreport, "this is a fourth test message to the conversation");
$conversation->sendMessage($autoreport, "this is a message to the conversation");
$conversation->sendMessage($autoreport, "this is also a message to the conversation");
$conversation->sendMessage($autoreport, "I am sending a lot of messages to this conversation");
$conversation->sendMessage($autoreport, "This conversation is full of my messages");
$conversation->sendMessage($autoreport, "This conversation is full of many of my messages");
$conversation->sendMessage($autoreport, "This conversation is full of a large amount of my messages");
$conversation->sendMessage($autoreport, "This conversation has messages by AutoReport");
$conversation->sendMessage($autoreport, "This conversation contains messages by AutoReport");
$conversation->sendMessage($autoreport, "This conversation includes messages by AutoReport");
$conversation->sendMessage($autoreport, "This is a test message by AutoReport");
$conversation->sendMessage($autoreport, "This is another test message by AutoReport");
$conversation->sendMessage($autoreport, "This is yet another test message by AutoReport");
$conversation->sendMessage($autoreport, "This test complements the list of test messages by AutoReport");
$conversation->sendMessage($autoreport, "This test message contains various characters: ↛ ħĽřƒƕƜƷǟʤϠℋℕℹ⅖Ⅲ");
$event = new ConversationAbandonEvent($conversation, $brad);
ConversationEvent::storeEvent($conversation->getId(), $event, Events::CONVERSATION_ABANDON);
$conversation->sendMessage($autoreport, "This is a message");
$conversation->sendMessage($autoreport, "This is another message");
$conversation->sendMessage($autoreport, "This is yet another message");

$conversation = Conversation::createConversation("Test", $alezakos->getId(), array($olfm, $reptitles, $fflood, $fradis, $lweak, $gsepar, $constitution));
$conversation->sendMessage($allejo, "then I can type at 12,000 wpm?");
$conversation->sendMessage($blast, "like:");
$event = new ConversationAbandonEvent($conversation, $brad);
ConversationEvent::storeEvent($conversation->getId(), $event, Events::CONVERSATION_ABANDON);
$event = new ConversationAbandonEvent($conversation, $mdskpr);
ConversationEvent::storeEvent($conversation->getId(), $event, Events::CONVERSATION_ABANDON);
$conversation->sendMessage($allejo, "tea?");
$event = new ConversationJoinEvent($conversation, array($brad, $mdskpr, $lweak));
ConversationEvent::storeEvent($conversation->getId(), $event, Events::CONVERSATION_JOIN);
$event = new ConversationRenameEvent($conversation, "test", "Test", $alezakos);
ConversationEvent::storeEvent($conversation->getId(), $event, Events::CONVERSATION_RENAME);
$conversation->sendMessage($allejo, "what would have some.");
$conversation->sendMessage($snake, "hahaha I'm sure I've used a url shorten urls book I'm not sure it would be used it");
$conversation->sendMessage($blast, "so I assume you're the correlative still always_ goes down history\"'s for anything");
$conversation->sendMessage($kierra, "I had a page. No addon, plugin to windows it...");
$conversation->sendMessage($allejo, "so I'm the world  (which was amazing :)");
$event = new ConversationKickEvent($conversation, $lweak, $alezakos);
ConversationEvent::storeEvent($conversation->getId(), $event, Events::CONVERSATION_KICK);
$event = new ConversationKickEvent($conversation, $brad, $alezakos);
ConversationEvent::storeEvent($conversation->getId(), $event, Events::CONVERSATION_KICK);
$conversation->sendMessage($autoreport, "she also going to essential command and to https://github Releases?\"  \"No!\"  \"STOP! IT!\"  \"*pbbbttt*\"  \"That's interesting I want help");
$conversation->sendMessage($autoreport, "I wonder 600KB) screenshot here. but how much I know all I was doing to debug, visual studio");
$conversation->sendMessage($autoreport, "and night night!");

echo " done!";

echo "\nAdding bans...";
Ban::addBan($snake->getId(), $alezakos->getId(), "2014-09-15", "Snarke 12534 has been barned again", "Cuz you're snake", "256.512.104.1");
Ban::addBan($allejo->getId(), $tw1sted->getId(), "2014-05-17", "for using 'dope'", "dope", array("127.0.2.1", "128.0.3.2"));
Ban::addBan($tw1sted->getId(), $alezakos->getId(), "2014-06-12", "tw1sted banned for being too awesome");
Ban::addBan($alezakos->getId(), $tw1sted->getId(), "2014-11-01", "alezakos banned for breaking the build", "For breaking the build", array("256.512.124.1", "256.512.124.3"));
echo " done!";

echo "\nAdding pages...";
Page::addPage("Rules", "<p>This is a test page.</p>\n<p>Let's hope this works!</p>", $tw1sted->getId());
Page::addPage("Contact", "<p>If you find anything wrong, please stop by irc.freenode.net channel #sujevo and let a developer know.<br /><br />Thanks", $tw1sted->getId());
echo " done!";

echo "\nAdding news categories...";
$announcements = NewsCategory::addCategory("Announcements");
$administration = NewsCategory::addCategory("Administration");
$events = NewsCategory::addCategory("Events");
$newFeatures = NewsCategory::addCategory("New Features");
echo " done!";

echo "\nAdding news entries...";
News::addNews("Announcement", "Very important Announcement", $kierra->getId(), $newFeatures->getId());
News::addNews("Cats think we are bigger cats", "In order for your indess recognizes where this whole mistake has come, and why one accuses the pleasure and praise the pain, and I will open to you all and set apart, what those founders of the truth and, as builders of the happy life himself has said about it. No one, he says, despise, or hate, or flee the desire as such, but because great pain to follow, if you do not pursue pleasure rationally. Similarly, the pain was loved as such by no one or pursues or desires, but because occasionally circumstances occur that one means of toil and pain can procure him some great pleasure to look verschaften be. To stay here are a trivial, so none of us would ever undertakes laborious physical exercise, except to obtain some advantage from it. But who is probably the blame, which requires an appetite, has no annoying consequences, or one who avoids a pain, which shows no desire? In contrast, blames and you hate with the law, which can soften and seduced by the allurements of present pleasure, without seeing in his blind desire which pain and inconvenience wait his reason. Same debt meet Those who from weakness, i.e to escape the work and the pain, neglect their duties. A person can easily and quickly make the real difference, to a quiet time where the choice of the decision is completely free and nothing prevents them from doing what we like best, you have to grasp every pleasure and every pain avoided, but to times it hits in succession of duties or guilty of factual necessity that you reject the desire and complaints must not reject. Why then the way will make a selection so that it Achieve a greater rejection by a desire for it or by taking over some pains to spare larger.", $alezakos->getId());
echo " done!";

echo "\n\nThe database has been populated successfully.\n";
