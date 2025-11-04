<?php

namespace Tests\Browser;

use Symfony\Component\Panther\PantherTestCase;
use Tests\TestKernel;
use Zenstruck\Browser\PantherBrowser;
use Zenstruck\Browser\Test\HasBrowser;

abstract class BrowserTestCase extends PantherTestCase
{
    use HasBrowser;

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected function myBrowser(): PantherBrowser
    {
        return $this->pantherBrowser(['external_base_uri' => 'http://localhost:8100']);
    }

    protected function asUser($login, $password)
    {
        return $this->myBrowser()
            ->visit('http://localhost:8100/wp-admin')
            ->fillField('user_login', $login)
            ->fillField('user_pass', $password)
            ->click('wp-submit')
            ->assertOn('http://localhost:8100/wp-admin/');
    }

    protected function asAdmin()
    {
        return $this->asUser('admin', 'admin');
    }

    protected function asAuthor()
    {
        return $this->asUser('author', 'author');
    }

    protected function asEditor()
    {
        return $this->asUser('editor', 'editor');
    }
}
