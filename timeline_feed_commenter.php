<?php
require __DIR__."/vendor/autoload.php";
require __DIR__."/config.php";

$instagram = new \InstagramAPI\Instagram(false, false);
$signature = \InstagramAPI\Signatures::generateUUID();

try
{
    echo("[?] Try for login as {$account['username']}...\n");
    $instagram->login($account['username'], $account['password']);
    echo("[!] Login successfully!\n");
    sleep(2);

    echo("[!] Getting timeline feeds...\n");
    $next_max_id = null;
    do
    {
        $feeds = $instagram->timeline->getTimelineFeed($next_max_id);
        foreach($feeds->getFeedItems() as $feed)
        {
            $random_comment = $timeline_feed_commenter['comments'][array_rand($timeline_feed_commenter['comments'])];
            if($feed->isMediaOrAd() == 1)
            {
                if($feed->getMediaOrAd()->isId() && empty($feed->getMediaOrAd()->isHasLiked()))
                {
                    if($timeline_feed_commenter['is_likes'] == 1)
                    {
                        $like = $instagram->media->like($feed->getMediaOrAd()->getId());
                        if($like->getStatus() == "ok")
                        {
                            echo "[+] ".date("d-m-Y H:i:s")." on ".$feed->getMediaOrAd()->getUser()->getUsername()."'s feed was liked.\n";
                        }
                        else
                        {
                            echo "[!] ".date("d-m-Y H:i:s")." on have a error, please wait for next job in {$timeline_feed_commenter['have_err']} seconds.\n";
                            sleep($timeline_feed_commenter['have_err']);
                        }
                    }
                    $comment = $instagram->media->comment($feed->getMediaOrAd()->getId(), $random_comment);
                    if($comment->getStatus() == "ok")
                    {
                        echo "[+] ".date("d-m-Y H:i:s")." on ".$feed->getMediaOrAd()->getUser()->getUsername()."'s feed was commented. => {$random_comment}\n";
                        sleep($timeline_feed_commenter['interval']);
                    }
                    else
                    {
                        echo "[!] ".date("d-m-Y H:i:s")." on have a error, please wait for next job in {$timeline_feed_commenter['have_err']} seconds.\n";
                        sleep($timeline_feed_commenter['have_err']);
                    }
                }
            }
        }
        $next_max_id = $feeds->getNextMaxId();
    }
    while($next_max_id !== null);
}
catch(Exception $e)
{
    echo("[!] ".$e->getMessage()."\n");
}