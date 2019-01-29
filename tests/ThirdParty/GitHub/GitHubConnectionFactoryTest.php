<?php
/**
 * @author Tharanga Kothalawala <tharanga.kothalawala@tsk-webdevelopment.com>
 * @date 29-01-2019
 */

namespace TSK\SSO\ThirdParty\GitHub;

use PHPUnit\Framework\TestCase;

class GitHubConnectionFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnAnInstanceOfAGitHubConnection()
    {
        $sut = new GitHubConnectionFactory('TestAppName');

        $actualConnection = $sut->get('clientId', 'clientSecret', 'www.tsk-webdevelopment.com');

        $this->assertInstanceOf('\TSK\SSO\ThirdParty\GitHub\GitHubConnection', $actualConnection);
    }
}
