<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Controller\PrettyUrls;

use EasyCorp\Bundle\EasyAdminBundle\Tests\PrettyUrlsTestApplication\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group pretty_urls
 */
class PrettyUrlsControllerTest extends WebTestCase
{
    public static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public function testGeneratedRoutes()
    {
        $expectedRoutes = [];
        // the default routes of DashboardController (which doesn't customize anything about them)
        $expectedRoutes['admin_pretty'] = '/admin/pretty/urls';
        $expectedRoutes['admin_pretty_blog_post_index'] = '/admin/pretty/urls/blog_post/';
        $expectedRoutes['admin_pretty_blog_post_detail'] = '/admin/pretty/urls/blog_post/{entityId}';
        $expectedRoutes['admin_pretty_blog_post_new'] = '/admin/pretty/urls/blog_post/new';
        $expectedRoutes['admin_pretty_blog_post_edit'] = '/admin/pretty/urls/blog_post/{entityId}/edit';
        $expectedRoutes['admin_pretty_blog_post_delete'] = '/admin/pretty/urls/blog_post/{entityId}/delete';
        $expectedRoutes['admin_pretty_blog_post_batchDelete'] = '/admin/pretty/urls/blog_post/batchDelete';
        $expectedRoutes['admin_pretty_blog_post_autocomplete'] = '/admin/pretty/urls/blog_post/autocomplete';
        $expectedRoutes['admin_pretty_category_index'] = '/admin/pretty/urls/category/';
        $expectedRoutes['admin_pretty_category_detail'] = '/admin/pretty/urls/category/{entityId}';
        $expectedRoutes['admin_pretty_category_new'] = '/admin/pretty/urls/category/new';
        $expectedRoutes['admin_pretty_category_edit'] = '/admin/pretty/urls/category/{entityId}/edit';
        $expectedRoutes['admin_pretty_category_delete'] = '/admin/pretty/urls/category/{entityId}/delete';
        $expectedRoutes['admin_pretty_category_batchDelete'] = '/admin/pretty/urls/category/batchDelete';
        $expectedRoutes['admin_pretty_category_autocomplete'] = '/admin/pretty/urls/category/autocomplete';
        // these are the routes related to the User entity; this is not used by DashboardController, but they are created
        // anyways because EasyAdmin creates routes for all dashboards + CRUD controllers by default (this can be avoided
        // by setting the 'allowedControllers' option in the #[AdminDashboard] attribute)
        $expectedRoutes['admin_pretty_external_user_editor_custom_route_for_index'] = '/admin/pretty/urls/user-editor/custom/path-for-index';
        $expectedRoutes['admin_pretty_external_user_editor_custom_route_for_new'] = '/admin/pretty/urls/user-editor/new';
        $expectedRoutes['admin_pretty_external_user_editor_batchDelete'] = '/admin/pretty/urls/user-editor/batchDelete';
        $expectedRoutes['admin_pretty_external_user_editor_autocomplete'] = '/admin/pretty/urls/user-editor/autocomplete';
        $expectedRoutes['admin_pretty_external_user_editor_edit'] = '/admin/pretty/urls/user-editor/{entityId}/edit';
        $expectedRoutes['admin_pretty_external_user_editor_delete'] = '/admin/pretty/urls/user-editor/{entityId}/delete';
        $expectedRoutes['admin_pretty_external_user_editor_detail'] = '/admin/pretty/urls/user-editor/custom/path-for-detail/{entityId}';
        // the fully-customized routes of the SecondDashboardController; EasyAdmin only generates routes for the User
        // entity because it's the only one allowed by the 'allowedControllers' option in the #[AdminDashboard] attribute
        $expectedRoutes['second_dashboard'] = '/second/dashboard';
        $expectedRoutes['second_dashboard_external_user_editor_custom_route_for_index'] = '/second/dashboard/user-editor/custom/path-for-index';
        $expectedRoutes['second_dashboard_external_user_editor_custom_route_for_new'] = '/second/dashboard/user-editor/add-new';
        $expectedRoutes['second_dashboard_external_user_editor_batchDelete'] = '/second/dashboard/user-editor/batchDelete';
        $expectedRoutes['second_dashboard_external_user_editor_autocomplete'] = '/second/dashboard/user-editor/autocomplete';
        $expectedRoutes['second_dashboard_external_user_editor_change'] = '/second/dashboard/user-editor/edit/---{entityId}---';
        $expectedRoutes['second_dashboard_external_user_editor_delete_this_now'] = '/second/dashboard/user-editor/{entityId}/delete';
        $expectedRoutes['second_dashboard_external_user_editor_detail'] = '/second/dashboard/user-editor/custom/path-for-detail/{entityId}';
        $expectedRoutes['admin_pretty_external_user_editor_foobar'] = '/admin/pretty/urls/user-editor/bar/foo';
        $expectedRoutes['second_dashboard_external_user_editor_foobar'] = '/second/dashboard/user-editor/bar/foo';

        self::bootKernel();
        $container = static::getContainer();
        $router = $container->get('router');
        $generatedRoutes = [];
        foreach ($router->getRouteCollection() as $name => $route) {
            $generatedRoutes[$name] = $route->getPath();
        }

        ksort($generatedRoutes);
        ksort($expectedRoutes);

        $this->assertEquals($expectedRoutes, $generatedRoutes);
    }

    public function testDefaultWelcomePage()
    {
        $client = static::createClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/admin/pretty/urls');

        $this->assertResponseIsSuccessful();

        $this->assertSame('http://localhost/admin/pretty/urls/blog_post/', $crawler->filter('li.menu-item a:contains("Blog Posts")')->attr('href'));
        $this->assertSame('http://localhost/admin/pretty/urls/category/', $crawler->filter('li.menu-item a:contains("Categories")')->attr('href'));
    }

    public function testCusomizedWelcomePage()
    {
        $client = static::createClient();
        $client->followRedirects();

        $client->request('GET', '/second/dashboard');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Welcome to EasyAdmin 4');
    }

    public function testDefaultCrudController()
    {
        $client = static::createClient();
        $client->followRedirects();

        $client->request('GET', '/admin/pretty/urls/blog_post');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1.title', 'BlogPost');
    }

    public function testCustomizedCrudController()
    {
        $client = static::createClient();
        $client->followRedirects();

        $client->request('GET', '/second/dashboard/user-editor/custom/path-for-index');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1.title', 'User');
    }

    public function testDefaultMainMenuUsesPrettyUrls()
    {
        $client = static::createClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/admin/pretty/urls/blog_post');

        $this->assertSame('/admin/pretty/urls', $crawler->filter('#header-logo a.logo')->attr('href'), 'The main Dashboard logo link points to the dashboard entry URL');
        $this->assertSame('http://localhost/admin/pretty/urls/blog_post/', $crawler->filter('li.menu-item a:contains("Dashboard")')->attr('href'), 'The Dashboard link inside the menu points to the first entity of the menu');
        $this->assertSame('http://localhost/admin/pretty/urls/blog_post/', $crawler->filter('li.menu-item a:contains("Blog Posts")')->attr('href'));
        $this->assertSame('http://localhost/admin/pretty/urls/category/', $crawler->filter('li.menu-item a:contains("Categories")')->attr('href'));
    }

    public function testCustomMainMenuUsesPrettyUrls()
    {
        $client = static::createClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/second/dashboard/user-editor/custom/path-for-index');

        $this->assertSame('/second/dashboard', $crawler->filter('#header-logo a.logo')->attr('href'), 'The main Dashboard logo link points to the dashboard entry URL');
        $this->assertSame('http://localhost/second/dashboard/user-editor/custom/path-for-index', $crawler->filter('li.menu-item a:contains("Dashboard")')->attr('href'), 'The Dashboard link inside the menu points to the first entity of the menu');
        $this->assertSame('http://localhost/second/dashboard/user-editor/custom/path-for-index', $crawler->filter('li.menu-item a:contains("Users")')->attr('href'));
    }

    public function testDefaultActionsUsePrettyUrls()
    {
        $client = static::createClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/admin/pretty/urls/blog_post');

        $this->assertSame('http://localhost/admin/pretty/urls/blog_post/?page=1', $crawler->filter('form.form-action-search')->attr('action'));
        $this->assertSame('http://localhost/admin/pretty/urls/blog_post/new', $crawler->filter('.global-actions a.action-new')->attr('href'));
        $this->assertSame('http://localhost/admin/pretty/urls/blog_post/1/edit', $crawler->filter('td a.action-edit')->attr('href'));
        $this->assertSame('http://localhost/admin/pretty/urls/blog_post/1/delete', $crawler->filter('td a.action-delete')->attr('href'));
        $this->assertMatchesRegularExpression('#http://localhost/admin/pretty/urls/blog_post/1/edit\?csrfToken=.*&fieldName=content#', $crawler->filter('td.field-boolean input[type="checkbox"]')->attr('data-toggle-url'));
    }

    public function testCustomActionsUsePrettyUrls()
    {
        $client = static::createClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/second/dashboard/user-editor/custom/path-for-index');

        $this->assertSame('http://localhost/second/dashboard/user-editor/custom/path-for-index?page=1', $crawler->filter('form.form-action-search')->attr('action'));
        $this->assertSame('http://localhost/second/dashboard/user-editor/add-new', $crawler->filter('.global-actions a.action-new')->attr('href'));
        $this->assertSame('http://localhost/second/dashboard/user-editor/edit/---1---', $crawler->filter('td a.action-edit')->attr('href'));
        $this->assertSame('http://localhost/second/dashboard/user-editor/1/delete', $crawler->filter('td a.action-delete')->attr('href'));
    }

    public function testDefaultSortLinksUsePrettyUrls()
    {
        $client = static::createClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/admin/pretty/urls/blog_post');

        $this->assertSame('http://localhost/admin/pretty/urls/blog_post/?page=1&sort%5Bid%5D=DESC', $crawler->filter('th.searchable a')->eq(0)->attr('href'));
        $this->assertSame('http://localhost/admin/pretty/urls/blog_post/?page=1&sort%5Btitle%5D=DESC', $crawler->filter('th.searchable a')->eq(1)->attr('href'));
        $this->assertSame('http://localhost/admin/pretty/urls/blog_post/?page=1&sort%5Bslug%5D=DESC', $crawler->filter('th.searchable a')->eq(2)->attr('href'));
        $this->assertSame('http://localhost/admin/pretty/urls/blog_post/?page=1&sort%5Bcontent%5D=DESC', $crawler->filter('th.searchable a')->eq(3)->attr('href'));
        $this->assertSame('http://localhost/admin/pretty/urls/blog_post/?page=1&sort%5Bauthor%5D=DESC', $crawler->filter('th.searchable a')->eq(4)->attr('href'));
    }

    public function testCustomSortLinksUsePrettyUrls()
    {
        $client = static::createClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/second/dashboard/user-editor/custom/path-for-index');

        $this->assertSame('http://localhost/second/dashboard/user-editor/custom/path-for-index?page=1&sort%5Bid%5D=DESC', $crawler->filter('th.searchable a')->eq(0)->attr('href'));
        $this->assertSame('http://localhost/second/dashboard/user-editor/custom/path-for-index?page=1&sort%5Bname%5D=DESC', $crawler->filter('th.searchable a')->eq(1)->attr('href'));
        $this->assertSame('http://localhost/second/dashboard/user-editor/custom/path-for-index?page=1&sort%5Bemail%5D=DESC', $crawler->filter('th.searchable a')->eq(2)->attr('href'));
    }
}
