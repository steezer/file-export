<?php

namespace FileExport;

class ParserXMind
{

    public static function parse($source, $baseDir)
    {
        $sourceJSON = json_decode($source, true);
        $data = self::doParse($sourceJSON, $baseDir);
        return $data;
    }

    private static function doParse($source, $baseDir)
    {
        // 附件
        $attachments = array();
        $data = array(
            'meta' => self::generateMeta(),
            'topic' => self::parseTopic($source, $attachments)
        );
        $content = Tpl::compile("xmind/content.xml", $data, $baseDir);
        $meta = Tpl::compile("xmind/meta.xml", null, $baseDir);
        $revisionsData = self::getRevisionsData();
        $revisions = Tpl::compile("xmind/revisions.xml", array(
            'meta' => $data['meta'],
            'revisions' => $revisionsData
        ), $baseDir);
        $rev = Tpl::compile("xmind/rev.xml", $data, $baseDir);
        $manifest = Tpl::compile("xmind/manifest.xml", array(
            'meta' => $data['meta'],
            'revisions' => $revisionsData,
            'attachments' => $attachments
        ), $baseDir);
        $path = self::write($data['meta'], array(
            'content' => $content,
            'meta' => $meta,
            'revisions' => $revisions,
            'rev' => $rev,
            'manifest' => $manifest,
            'attachments' => $attachments
        ));

        $archivePath = Ziper::compress($path);

        return self::move($data['meta'], $archivePath, $baseDir);
    }

    /**
     * topic 解析
     * @param array $source KM数据源
     * @return 解析后的topic数据
     */
    private static function parseTopic(&$source, &$attachments)
    {
        if (!array_key_exists("data", $source)) {
            return $source;
        }

        $source['meta'] = self::getTimeAndId();
        self::parseFlag($source);
        self::parseImage($source, $attachments);
        if (!array_key_exists("children", $source)) {
            return $source;
        }

        foreach ($source["children"] as &$child) {
            self::parseTopic($child, $attachments);
        }
        return $source;
    }

    private static function generateMeta()
    {
        return self::getTimeAndId();
    }

    private static function getRevisionsData()
    {
        return self::getTimeAndId();
    }

    private static function write($meta, $data)
    {
        $root = self::getTmpDir();
        FileHelper::write($root, 'content.xml', $data['content']);
        FileHelper::write($root, 'Revisions/' . $meta['id'] . '/revisions.xml', $data['revisions']);
        FileHelper::write($root, 'Revisions/' . $meta['id'] . '/rev-1-' . $meta['timestamp'] . '.xml', $data['rev']);
        FileHelper::write($root, 'meta.xml', $data['meta']);
        FileHelper::write($root, '/META-INF/manifest.xml', $data['manifest']);
        if (count($data['attachments']) > 0) {
            mkdir($root . 'attachments');
            foreach ($data['attachments'] as $attach) {
                rename($attach['origin'], $root . $attach['filepath']);
            }
        }
        return $root;
    }

    private static function parseFlag(&$source)
    {
        $mapping = array(
            'task-start', 'task-oct', 'task-quarter', 'task-3oct',
            'task-half', 'task-5oct', 'task-3quar', 'task-7oct', 'task-done'
        );
        if (isset($source['data']['progress'])) {
            $source['data']['progress'] = $mapping[$source['data']['progress'] - 1];
        }
        if (isset($source['data']['resource'])) {
            $source['data']['resource'] = join(",", $source['data']['resource']);
        }
    }

    private static function parseImage(&$source, &$attachments)
    {
        if (!isset($source['data']['image'])) {
            return;
        }
        $image = ImageCapture::capture($source['data']['image']);
        if ($image === null) {
            unset($source['data']['image']);
            return;
        }

        $info = self::getTimeAndId();
        $imagePath = $image['filepath'];
        $source['data']['image'] = array(
            'filepath' => 'attachments/' . $info['id'] . $image['suffix'],
            'meta' => $info
        );
        $attachments[] = array(
            'filepath' => 'attachments/' . $info['id'] . $image['suffix'],
            'origin' => $imagePath,
            'type' => $image['mime'],
            'meta' => $info
        );
    }

    private static function getTmpDir()
    {
        $tmpDir = sys_get_temp_dir() . '/_mindtmp/';
        while (true) {
            $timestamp = intval(microtime(true) * 1000);
            if (!file_exists($timestamp)) {
                return $tmpDir . $timestamp . '/';
            }
        }
    }

    private static function move($meta, $path, $baseDir)
    {
        $savepath = (!empty($baseDir) ? $baseDir : dirname(__DIR__)) . DS . 'upload' . DS;
        if (!file_exists($savepath)) {
            mkdir($savepath, 0770, true);
        }
        $savepath = pathinfo($savepath . '/test', PATHINFO_DIRNAME);
        $filepath = $savepath . '/' . $meta['id'] . '.xmind';
        rename($path, $filepath);
        return $filepath;
    }

    private static function getTimeAndId()
    {
        $timestamp = intval(microtime(true) * 1000);
        return array(
            'timestamp' => $timestamp,
            'id' => md5($timestamp)
        );
    }
}
