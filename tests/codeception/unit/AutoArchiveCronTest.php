<?php

/**
 * Auto Archive
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace tests\codeception\unit\modules\autoArchive;

use humhub\modules\autoArchive\Events;
use humhub\modules\autoArchive\Module;
use humhub\modules\comment\models\Comment;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\Event;

/**
 * Default fixtures: all the content was created in 2014. Space 1 = contentcontainer 4,
 * Space 2 = contentcontainer 5 (Post 11, content id 11, is in Space 2).
 */
class AutoArchiveCronTest extends HumHubDbTestCase
{
    public function testActiveContainersAreFoundFromContentsAndComments(): void
    {
        $threshold = date('Y-m-d H:i:s', strtotime('-30 days'));
        $this->assertSame([], Events::findActiveContainerIds($threshold), 'Fixture content is old');

        // A new post in Space 1
        $this->becomeUser('Admin');
        $post = new Post(['message' => 'Recent post']);
        $post->content->container = Space::findOne(['id' => 1]);
        $this->assertTrue($post->save());

        // A new comment on an old post of Space 2
        $this->becomeUser('User2');
        $this->assertTrue((new Comment(['message' => 'Recent comment', 'content_id' => 11]))->save());

        $active = Events::findActiveContainerIds($threshold);
        $this->assertSame([4 => true, 5 => true], $active);

        // Content of a disabled space does not count
        Space::updateAll(['status' => Space::STATUS_DISABLED], ['id' => 1]);
        $this->assertSame([5 => true], Events::findActiveContainerIds($threshold));
    }

    public function testInactiveSpacesAreArchivedAndActiveOnesKept(): void
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('auto-archive');
        $module->settings->set('daysBeforeArchiving', 30);
        $module->settings->set('notifySpaceAdmin', false);

        $this->becomeUser('Admin');
        $post = new Post(['message' => 'Recent post']);
        $post->content->container = Space::findOne(['id' => 1]);
        $this->assertTrue($post->save());

        $this->becomeUser('User2');
        $this->assertTrue((new Comment(['message' => 'Recent comment', 'content_id' => 11]))->save());

        $output = '';
        $controller = new class ($output) {
            public function __construct(private string &$output)
            {
            }

            public function stdout(string $string, ...$args): void
            {
                $this->output .= $string;
            }
        };
        Events::onCronDailyRun(new Event(['sender' => $controller]));

        $this->assertSame(Space::STATUS_ENABLED, (int)Space::findOne(['id' => 1])->status, 'Recent content');
        $this->assertSame(Space::STATUS_ENABLED, (int)Space::findOne(['id' => 2])->status, 'Recent comment');
        $this->assertSame(Space::STATUS_ARCHIVED, (int)Space::findOne(['id' => 3])->status);
        $this->assertSame(Space::STATUS_ARCHIVED, (int)Space::findOne(['id' => 4])->status);
        $this->assertStringContainsString('archived', $output);
    }
}
