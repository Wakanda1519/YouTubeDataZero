<?php

namespace Wakanda\YouTubeDataZero;

class YouTubeDataZero
{
    private $apiKey;
    private $apiUrl = 'https://www.googleapis.com/youtube/v3/videos';

    public function __construct(string $apiKey)
    {
        if (empty($apiKey)) {
            throw new \Exception('[YouTubeDataZero] Не указан API Key.');
        }
        $this->apiKey = $apiKey;
    }

    public function getVideoInfo(string $url)
    {
        $videoId = $this->extractVideoId($url);

        if (!$videoId) {
            throw new \Exception('[YouTubeDataZero] Некорректная ссылка на видео.');
        }

        $params = [
            'id' => $videoId,
            'key' => $this->apiKey,
            'part' => 'snippet,statistics,contentDetails',
        ];

        $requestUrl = $this->apiUrl . '?' . http_build_query($params);
        $response = $this->makeRequest($requestUrl);
        
        $data = json_decode($response, true);

        if (isset($data['error'])) {
            $msg = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown API Error';
            throw new \Exception('[YouTubeDataZero] API Error: ' . $msg);
        }

        if (empty($data['items'])) {
            return null;
        }

        $item = $data['items'][0];

        $isoDuration = isset($item['contentDetails']['duration']) ? $item['contentDetails']['duration'] : null;

        return [
            'id'           => $item['id'],
            'title'        => $item['snippet']['title'],
            'description'  => $item['snippet']['description'],
            'channel'      => $item['snippet']['channelTitle'],
            'views'        => (int) $item['statistics']['viewCount'],
            'likes'        => isset($item['statistics']['likeCount']) ? (int) $item['statistics']['likeCount'] : 0,
            'thumbnail'    => $this->getBestThumbnail($item['snippet']['thumbnails']),
            'published_at' => $item['snippet']['publishedAt'],
            'duration_iso' => $isoDuration,
            'duration'     => $this->formatDuration($isoDuration)
        ];
    }

    private function extractVideoId(string $url)
    {
        $pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?|shorts)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';
        
        if (preg_match($pattern, $url, $match)) {
            return $match[1];
        }
        
        return false;
    }

    private function makeRequest(string $url)
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'YouTubeDataZero/1.0');

        $result = curl_exec($ch);
        $error = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);

        if ($error) {
            throw new \Exception('[YouTubeDataZero] cURL Error: ' . $error);
        }

        if ($statusCode !== 200) {
            throw new \Exception('[YouTubeDataZero] HTTP Error Code: ' . $statusCode . '. Response: ' . $result);
        }

        return $result;
    }

    private function getBestThumbnail(array $thumbnails)
    {
        if (isset($thumbnails['maxres'])) return $thumbnails['maxres']['url'];
        if (isset($thumbnails['standard'])) return $thumbnails['standard']['url'];
        if (isset($thumbnails['high'])) return $thumbnails['high']['url'];
        if (isset($thumbnails['medium'])) return $thumbnails['medium']['url'];
        return isset($thumbnails['default']) ? $thumbnails['default']['url'] : null;
    }

    /**
     * Преобразует ISO 8601 duration (PT1H2M10S) в читаемый формат (01:02:10)
     */
    private function formatDuration($isoDuration)
    {
        if (!$isoDuration) return null;

        try {
            $interval = new \DateInterval($isoDuration);
            return $interval->format('%H:%I:%S');
        } catch (\Exception $e) {
            return null;
        }
    }
}
