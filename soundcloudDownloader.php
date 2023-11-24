<?php
class soundcloudDownloader
{
    public $client_id = false;
    private function findDownloadLink(array $v)
    {
        foreach ($v['media']['transcodings'] as $t) if (stristr($t['url'], 'progressive')) {
            if (!$this->client_id) $this->clientId();
            return $this->get($t['url'] . "?client_id=" . $this->client_id . "&track_authorization=" . $v['track_authorization'])['url'];
        }
        return false;
    }
    private function resolveShortUrl($url)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(CURLOPT_FOLLOWLOCATION => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false));
        $response = curl_exec($ch);
        if ($response === false) return false;
        return curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    }
    private function get(string $path, array $query = []): array
    {
        if (count($query) != 0) {
            $params = [];
            foreach ($query as $k => $v) $params[] = "{$k}={$v}";
            $params = join('&', $params);
            $path .= "?{$params}";
        }
        return json_decode(file_get_contents($path), true);
    }
    public function setClientId(string $token)
    {
        $this->client_id = $token;
    }
    public function clientId(): string
    {
        $data = file_get_contents('https://soundcloud.com');
        $data = substr($data, strlen($data) - 180, strlen($data));
        $x1 = strpos($data, 'src="') + strlen('src="');
        $data = substr($data, $x1, strpos($data, '.js', $x1) - $x1 + 3);
        $data = file_get_contents($data);
        $x1 = strpos($data, 'client_id:"') + strlen('client_id:"');
        $data = substr($data, $x1, strpos($data, '"', $x1) - $x1);
        $this->client_id = $data;
        return $data;
    }
    public function search(string $value, int $limit = 20, int $offset = 0): array
    {
        if (!$this->client_id) $this->clientId();
        return $this->get("https://api-v2.soundcloud.com/search", ['client_id' => $this->client_id, 'q' => urlencode($value), 'offset' => $offset, 'limit' => $limit]);
    }
    public function urlData(string $url)
    {
        $run = true;
        $url_info = parse_url($url);
        $url_info['host'] = str_replace("www.", "", strtolower($url_info['host']));
        if ($url_info['host'] != 'soundcloud.com') {
            $url = $this->resolveShortUrl($url);
            $url_info = parse_url($url);
            $url_info['host'] = str_replace("www.", "", strtolower($url_info['host']));
            if ($url_info['host'] != 'soundcloud.com') $run = false;
            else $url = "https://".$url_info['host'].$url_info['path'];
        }
        if ($run) {
            $data = file_get_contents($url);
            $x = strpos($data, "__sc_hydration =") + strlen("__sc_hydration =");
            $data = substr($data, $x, strpos($data, ";</", $x) - $x);
            $data = json_decode($data, true);
            foreach ($data as $k => $v) if (!in_array($v['hydratable'], ['sound', 'user', 'playlist'])) unset($data[$k]);
            foreach ($data as $v) if ($v['hydratable'] == 'sound') {
                $dl = $this->findDownloadLink($v['data']);
                if ($dl != false) $data[] = ['download_url' => $dl];
            }
            return array_values($data);
        } else return [];
    }
    public function track(string $id): array
    {
        if (!$this->client_id) $this->clientId();
        $data = $this->get("https://api-v2.soundcloud.com/tracks", ['client_id' => $this->client_id, 'ids' => $id]);
        $dl = $this->findDownloadLink($data[0]);
        if ($dl != false) $data[] = ['download_url' => $dl];
        return $data;
    }
    public function userAlbums(string $id, int $limit = 20, int $offset = 0): array
    {
        if (!$this->client_id) $this->clientId();
        return $this->get("https://api-v2.soundcloud.com/users/{$id}/albums", ['client_id' => $this->client_id, 'offset' => $offset, 'limit' => $limit]);
    }
    public function userTracks(string $id, int $limit = 20, int $offset = 0): array
    {
        if (!$this->client_id) $this->clientId();
        return $this->get("https://api-v2.soundcloud.com/users/{$id}/tracks", ['client_id' => $this->client_id, 'offset' => $offset, 'limit' => $limit]);
    }
    public function userFollowings(string $id, int $limit = 20, int $offset = 0): array
    {
        if (!$this->client_id) $this->clientId();
        return $this->get("https://api-v2.soundcloud.com/users/{$id}/followings", ['client_id' => $this->client_id, 'offset' => $offset, 'limit' => $limit]);
    }
    public function userSpotlights(string $id, int $limit = 20, int $offset = 0): array
    {
        if (!$this->client_id) $this->clientId();
        return $this->get("https://api-v2.soundcloud.com/users/{$id}/spotlight", ['client_id' => $this->client_id, 'offset' => $offset, 'limit' => $limit]);
    }
    public function userLikes(string $id, int $limit = 20, int $offset = 0): array
    {
        if (!$this->client_id) $this->clientId();
        return $this->get("https://api-v2.soundcloud.com/users/{$id}/likes", ['client_id' => $this->client_id, 'offset' => $offset, 'limit' => $limit]);
    }
    public function userFeaturedProfiles(string $id, int $limit = 20, int $offset = 0): array
    {
        if (!$this->client_id) $this->clientId();
        return $this->get("https://api-v2.soundcloud.com/users/{$id}/featured-profiles", ['client_id' => $this->client_id, 'offset' => $offset, 'limit' => $limit]);
    }
    public function userTopTracks(string $id, int $limit = 20, int $offset = 0): array
    {
        if (!$this->client_id) $this->clientId();
        return $this->get("https://api-v2.soundcloud.com/users/{$id}/toptracks", ['client_id' => $this->client_id, 'offset' => $offset, 'limit' => $limit]);
    }
    public function userReposts(string $id, int $limit = 20, int $offset = 0): array
    {
        if (!$this->client_id) $this->clientId();
        return $this->get("https://api-v2.soundcloud.com/users/{$id}/reposts", ['client_id' => $this->client_id, 'offset' => $offset, 'limit' => $limit]);
    }
}
