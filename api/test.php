<?php

require_once __DIR__ . '/app/Customize/api/admin/plugin/extra/app.php';

$p_m1 = __DIR__ . '/pornhub_master.m3u8';
$p_m2 = __DIR__ . '/pornhub_videos.m3u8';

$m_tool = new M3U8Tool($p_m1);

//$m_tool->getDefinitions();
$m_tool->getSequences();


/**
 * m3u8 工具类
 * Class M3U8Parser
 */
class M3U8Tool
{
    /**
     * 内容
     *
     * @var string
     */
    private $content;

    /**
     * 清晰度
     *
     * @var array
     */
    private $definition = [];

    public function __construct(string $file)
    {
        $this->content = file_exists($file) ? file_get_contents($file) : $file;
        if (!$this->isM3u8()) {
            throw new Exception('提供文件或内容非M3U8格式');
        }
    }


    public function isM3u8(): bool
    {
        return preg_match('/^#EXTM3U/' , $this->content) > 0;
    }

    /**
     * 获取文件类型
     * source - 视频源（不同清晰度的视频源）
     * playlist - 切片列表
     * @return string source | sequence
     */
    public function getType(): string
    {
        if (preg_match('/#EXT-X-STREAM-INF:/' , $this->content) > 0) {
            return 'source';
        }
        if (preg_match('/#EXTINF:/' , $this->content) > 0) {
            return 'sequence';
        }
        throw new Exception('未知的文件类型');
    }

    /**
     * 获取视频清晰度
     * @return array
     */
    public function getDefinitions(): array
    {
        preg_match_all('/#EXT-X-STREAM-INF:(.*?)RESOLUTION=(\w+x\w+)/' , $this->content , $matches);
        $definition = $matches[2];
        return $definition;
    }

    /**
     * 获取切片列表
     * @return array
     */
    public function getSequences(): array
    {
        preg_match_all('/#EXTINF:.*(\n|\r|\n\r|\r\n)(.*?)\1/' , $this->content , $matches);
        $definition = $matches[2];
        return $definition;
    }

}
