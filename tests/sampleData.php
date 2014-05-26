#!/usr/bin/env php
<?php

require_once(__DIR__ . "/../bzion-load.php");

if (!DEVELOPMENT) {
    die("Populating the database with sample data isn't allowed in Production mode.\n");
}

$testPlayer = Player::getFromBZID(55976);
if ($testPlayer->isValid()) {
    die("Please clear your current data in the database or you'll end up with duplicate entries.\n");
}

$db = Database::getInstance();
$faker = Faker\Factory::create();

echo "Adding players...";
$alezakos   = Player::newPlayer(4944, "vehemently", null, "active", Player::DEVELOPER, "", "Sample description");
$allejo     = Player::newPlayer(31098, "allejo", null, "active", Player::DEVELOPER);
$ashvala    = Player::newPlayer(34353, "ashvala", null, "active", Player::DEVELOPER);
$autoreport = Player::newPlayer(55976, "AutoReport");
$blast      = Player::newPlayer(180, "blast", null, "active", Player::S_ADMIN);
$kierra     = Player::newPlayer(2229, "kierra", null, "active", Player::ADMIN);
$mdskpr     = Player::newPlayer(8312, "mdskpr");
$snake      = Player::newPlayer(54497, "Snake12534");
$tw1sted    = Player::newPlayer(9736, "tw1sted", null, "active", Player::DEVELOPER);
$brad       = Player::newPlayer(3030, "brad", null, "active", Player::S_ADMIN, "", "I keep nagging about when this project will be done");

for ($bzid = 400; $bzid <= 1000; $bzid++)
{
    if ($bzid%100 == 0) echo " $bzid...";
    Player::newPlayer($bzid, $faker->userName, null, "active", Player::PLAYER, "", $faker->realText);
}


echo " done!";

echo "\nAdding teams...";


for ($i = 1; $i < 85; $i++) {
    $leader = Player::newPlayer(5000+$i, $faker->userName, null, "active", Player::S_ADMIN, "", $faker->realText);
    Team::createTeam($faker->company, $leader->getId(), "", $faker->realText);
}

echo " done!";

echo "\nAdding members to teams...";

for ($id = 1; $id <= 400; $id++)
{
    if ($id%100 == 0) echo " $id...";
    $team = new Team(mt_rand(1,80));
    $team->addMember($id);
}

echo " done!";

echo "\nAdding matches...";

for ($id = 1; $id <= 2000; $id++)
{
    if ($id%100 == 0) echo " $id...";
    $team = new Team(mt_rand(1,80));
    $leader = $team->getLeader()->getId();
    Match::enterMatch(mt_rand(1,80), mt_rand(1,80), mt_rand(0,5), mt_rand(0,5), 20, $leader);
}
echo " done!";

echo "\nAdding servers...";
Server::addServer("BZPro Public HiX FFA", "bzpro.net:5154", $tw1sted->getId());
Server::addServer("BZPro Public HiX Rabbit Chase", "bzpro.net:5155", $tw1sted->getId());
echo " done!";


echo "\nAdding pages...";
Page::addPage("Rules", "<p>This is a test page.</p>\n<p>Lets hope this works!</p>", $tw1sted->getId());
Page::addPage("Contact", "<p>If you find anything wrong, please stop by irc.freenode.net channel #sujevo and let a developer know.<br /><br />Thanks", $tw1sted->getId());

for ($i = 0; $i < 10; $i++) {
    $team = new Team(mt_rand(1,80));
    $leader = $team->getLeader()->getId();

    Page::addPage($faker->word, $faker->text(800), $leader);
}
echo " done!";

echo "\nAdding news categories...";
$announcements = NewsCategory::addCategory("Announcements");
$administration = NewsCategory::addCategory("Administration");
$events = NewsCategory::addCategory("Events");
$newFeatures = NewsCategory::addCategory("New Features");

for ($i=0; $i<=10; $i++) {
    NewsCategory::addCategory($faker->cityPrefix);
}
echo " done!";

echo "\nAdding news entries...";
News::addNews("Announcement", "Very important Announcement", $kierra->getId(), $newFeatures->getId());
News::addNews("Cats think we are bigger cats", "In order for your indess recognizes where this whole mistake has come, and why one accuses the pleasure and praise the pain, and I will open to you all and set apart, what those founders of the truth and, as builders of the happy life himself has said about it. No one, he says, despise, or hate, or flee the desire as such, but because great pain to follow, if you do not pursue pleasure rationally. Similarly, the pain was loved as such by no one or pursues or desires, but because occasionally circumstances occur that one means of toil and pain can procure him some great pleasure to look verschaften be. To stay here are a trivial, so none of us would ever undertakes laborious physical exercise, except to obtain some advantage from it. But who is probably the blame, which requires an appetite, has no annoying consequences, or one who avoids a pain, which shows no desire? In contrast, blames and you hate with the law, which can soften and seduced by the allurements of present pleasure, without seeing in his blind desire which pain and inconvenience wait his reason. Same debt meet Those who from weakness, i.e to escape the work and the pain, neglect their duties. A person can easily and quickly make the real difference, to a quiet time where the choice of the decision is completely free and nothing prevents them from doing what we like best, you have to grasp every pleasure and every pain avoided, but to times it hits in succession of duties or guilty of factual necessity that you reject the desire and complaints must not reject. Why then the way will make a selection so that it Achieve a greater rejection by a desire for it or by taking over some pains to spare larger.", $alezakos->getId());

for ($i = 0; $i < 10; $i++) {
    $team = new Team(mt_rand(1,80));
    $leader = $team->getLeader()->getId();

    News::addNews($faker->word, $faker->text(800), $leader, mt_rand(1,10));
}
echo " done!";

echo "\n\nThe database has been populated successfully.\n";
