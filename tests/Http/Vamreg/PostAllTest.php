<?php

declare(strict_types=1);

require_once __DIR__ . '/VamregHelper.php';

/**
 * T283 (HTTP part) - Vamreg::post_all and post_all_out when there is nothing to send.
 *
 * The Vamreg server url is hard coded in the Vamregclient library and the controller creates the
 * client itself, so over HTTP a test must never post a DRAFT row (it would call the real server).
 * Building the declarations and the 200/400/403/500 answers are tested in-process in
 * tests/Models/VamregPostAllTest.php. Here: only the answer {status:-1} when the quarter has no DRAFT.
 *
 * Rules: only rows with status DRAFT and a date inside the quarter (inclusive) are sent; with none
 * the answer is JSON {status: -1, status_html: ...} and no row changes.
 */
final class PostAllTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAs('admin');
    }

    public function testWithoutDraftsInTheQuarterTheAnswerIsMinusOne(): void
    {
        // rows that are not DRAFT, and a DRAFT outside the quarter, do not count
        $sent = VamregHelper::in('2031-02-10', ['status' => 'SENT']);
        $invalid = VamregHelper::in('2031-02-11', ['status' => 'INVALID']);
        $error = VamregHelper::in('2031-02-12', ['status' => 'ERROR']);
        VamregHelper::in('2031-04-01', ['status' => 'DRAFT']);
        VamregHelper::in('2030-12-31', ['status' => 'DRAFT']);

        $response = $this->get('vamreg/post_all/2031/1');

        $this->assertStatus($response, 200);
        $this->assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));
        $body = $this->json($response);
        $this->assertSame(-1, $body['status']);
        $this->assertStringContainsString('There are no new draft declarations to send', $body['status_html']);
        $this->assertSame('SENT', $this->find('vamreg_in_buffer', $sent)['status']);
        $this->assertSame('INVALID', $this->find('vamreg_in_buffer', $invalid)['status']);
        $this->assertSame('ERROR', $this->find('vamreg_in_buffer', $error)['status']);
    }

    public function testTheOutRegisterHasItsOwnUrlAndAlsoAnswersMinusOne(): void
    {
        VamregHelper::out('2031-02-10', ['status' => 'SENT']);
        VamregHelper::out('2031-02-11', ['status' => 'INVALID']);
        VamregHelper::out('2031-07-01', ['status' => 'DRAFT']);

        $this->assertSame(-1, $this->json($this->get('vamreg/post_all_out/2031/1'))['status']);
        // the model segment of post_all selects the register; anything but "IN" is OUT
        $this->assertSame(-1, $this->json($this->get('vamreg/post_all/2031/1/OUT'))['status']);
    }

    public function testTheRegistersAreIndependent(): void
    {
        // a draft in IN does not make OUT think it has work; the IN draft is in a quarter we do not post
        VamregHelper::in('2033-05-05', ['status' => 'DRAFT']);

        $this->assertSame(-1, $this->json($this->get('vamreg/post_all_out/2033/2'))['status']);
        // and the dashboard of that quarter still offers the IN send button (positive control: the draft is there)
        Factory::setting('vamreg_api_key', 'test-key');
        $this->assertSee($this->get('vamreg/index/2033/2'), 'Post IN drafts');
    }

    public function testThePostButtonsFollowTheDraftCounts(): void
    {
        Factory::setting('vamreg_api_key', 'test-key');
        VamregHelper::out('2034-02-10', ['status' => 'DRAFT']);

        $page = $this->text($this->get('vamreg/index/2034/1'));

        $this->assertStringContainsString('IN up to date', $page);
        $this->assertStringContainsString('Post OUT drafts', $page);
        $this->assertStringContainsString('vamreg/post_all_out/2034/1', $page);
    }
}
