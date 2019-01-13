<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 31-12-2018
 *
 * Goto https://console.developers.google.com to create a test Google App which takes just 5 minutes ;)
 */

use TSK\SSO\ThirdParty;

include_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/DemoAppThirdPartyStorageRepository.php';
include_once __DIR__ . '/DemoAppUserRepository.php';

session_start();

$exampleAppUserRepository = new DemoAppUserRepository(__DIR__ . '/store');
$storageRepository = new DemoAppThirdPartyStorageRepository(__DIR__ . '/store');

$loggedInUser = null;
if (!empty($_SESSION['userEmail'])) {
    $loggedInUser = $exampleAppUserRepository->getUserAsArray($_SESSION['userEmail']);
    if (empty($loggedInUser)) {
        session_destroy();
    }
}
?>
<html>
    <head>
        <title>TSK Single Sign On Demo</title>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" rel="stylesheet" />
        <style>
            .thirdparty-connection-list{
                list-style-type: none;
            }
        </style>
    </head>
    <body>
        <div style="margin: 0 auto; width: 1000px;">

            <?php if (!empty($_SESSION['error'])) { ?>
                <div class="alert alert-danger"><strong>Error!</strong> <?php echo $_SESSION['error']; ?></div>
            <?php } elseif (!empty($_SESSION['success'])) { ?>
                <div class="alert alert-success"><strong>Success!</strong> <?php echo $_SESSION['success']; ?></div>
            <?php } ?>

            <?php if (empty($loggedInUser)) { ?>
                <h1>Welcome Guest!</h1>
            <?php } else { ?>
                <h1>Welcome <?php echo $loggedInUser['name']; ?>!</h1>
                <br/><br/>
                <h3>Profile</h3>
                <p>
                    <b>ID</b> : <?php echo $loggedInUser['id']; ?><br/>
                    <b>Name</b> : <?php echo $loggedInUser['name']; ?><br/>
                    <b>Email</b>: <?php echo $loggedInUser['email']; ?><br/>
                    <b>Picture</b> : <img src="<?php echo $loggedInUser['picture']; ?>" width="30"><br/>
                    <b>Gender</b> : <?php echo $loggedInUser['gender']; ?><br/>
                </p>
                <br/>
                <h3>Your Connections</h3>
                <ul style="list-style-type: none;">
                <?php
                    $thirdPartySignInVendors = array(
                        ThirdParty::FACEBOOK => array('profile' => 'https://www.facebook.com/%s', 'accounts' => array()),
                        ThirdParty::GOOGLE => array('profile' => 'https://plus.google.com/%s', 'accounts' => array()),
                        ThirdParty::SLACK => array('profile' => 'https://%s.slack.com/account/profile', 'accounts' => array()),
                        ThirdParty::LINKEDIN => array('profile' => 'https://linkedin.com/pub/%s', 'accounts' => array()),
                        ThirdParty::TWITTER => array('profile' => 'https://twitter.com/%s', 'accounts' => array()),
                    );
                    $vendorAccounts = $storageRepository->getByUserId($loggedInUser['id']);
                    foreach ($vendorAccounts as $vendorAccount) {
                        $thirdPartySignInVendors[$vendorAccount->vendorName()]['accounts'][] = $vendorAccount;
                    }

                    $connectionsHtml = '';
                    foreach ($thirdPartySignInVendors as $vendor => $vendorData) {
                        $vendorDisplayName = ucfirst($vendor);
                        if (empty($vendorData['accounts'])) {
                            $connectionsHtml .= <<<HTML
                        <li>
                            <img width='24' src="/images/{$vendor}.png" style="opacity: 0.3;">
                            <span style="color: #999;">{$vendorDisplayName} - Not connected<a href='/sso.php?vendor={$vendor}&task=signin'>&nbsp;<span class='btn-link'>Connect</span></a></span>
                        </li>
HTML
                            ;
                            continue;
                        }
                        $accountBadgeDisplayed = false;
                        $i = 0;
                        /** @var \TSK\SSO\Storage\MappedUser $vendorAccount */
                        foreach ($vendorData['accounts'] as $vendorAccount) {
                            if (!$accountBadgeDisplayed) {
                                $accountBadgeDisplayed = true;
                                $connectionsHtml .= "<li><img width='24' src='/images/{$vendor}.png'>&nbsp;{$vendorDisplayName} - Connected <small><a href='/sso.php?vendor={$vendor}&task=signin'><span class='btn-link'>Connect another {$vendorDisplayName} account</span></a></small></li>";
                            }

                            $data = json_decode($vendorAccount->vendorData(), true);
                            $thirdPartyProfilePage = sprintf($vendorData['profile'], $data['id']);
                            $otherConnectDetails = "<li><small>{$vendorDisplayName} user : <a target='blank' title='click to view'  href='{$thirdPartyProfilePage}'><img src='{$data['avatar']}' width='18' /> {$data['name']} - ({$vendorAccount->vendorEmail()})</a></small>&nbsp;&nbsp;<a href='/sso.php?vendor={$vendor}&task=revoke&id={$i}'><span class='btn-link'>Disconnect</span></a></li>";

                            $connectionsHtml .= "<li><span><ul>{$otherConnectDetails}</ul></span></li>";
                            $i++;
                        }
                    }

                    echo $connectionsHtml;
                ?>
                </ul>
            <?php }?>
            <div style="width: 30%; margin: 50px auto 0;">
                <?php if (!empty($loggedInUser)) { ?>
                    <h3><a href="/sso.php?task=logout" title="Sign in with FaceBook">Logout</a></h3>
                <?php } else { ?>
                    <h3>Sign in</h3>

                    <a href="/sso.php?vendor=facebook&task=signin" title="Sign in with FaceBook"><img src="/images/facebook.png" width="46"></a>
                    <a href="/sso.php?vendor=google&task=signin" title="Sign in with Google"><img src="/images/google.png" width="42"></a>
                    <a href="/sso.php?vendor=twitter&task=signin" title="Sign in with Twitter"><img src="/images/twitter.png" width="42"></a>
                    <a href="/sso.php?vendor=slack&task=signin" title="Sign in with Slack"><img src="/images/slack.png" width="50"></a>
                    <a href="/sso.php?vendor=linkedin&task=signin" title="Sign in with LinkedIn"><img src="/images/linkedin.png" width="46"></a>
                <?php }?>
            </div>

        </div>
    </body>
</html>
<?php
unset($_SESSION['error']);
unset($_SESSION['success']);
?>
