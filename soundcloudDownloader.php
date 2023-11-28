<?php
class soundcloudDownloader
{
    public $client_id = false;
    private function findDownloadLink(array $v)
    {
        $data = [];
        foreach ($v['media']['transcodings'] as $t) {
            if (!$this->client_id) $this->clientId();
            $t['download'] = $this->get($t['url'] . "?client_id=" . $this->client_id . "&track_authorization=" . $v['track_authorization'])['url'];
            $data[] = $t;
        }
        return $data;
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

    public function download(string $url)
    {
        $url_info = parse_url($url);
        $ext = pathinfo($url_info['path'], PATHINFO_EXTENSION);
        if ($ext == 'mp3') return file_get_contents($url);
        else {
            $m3u_data = file_get_contents($url);
            $final = '';
            $lines = explode("\n", $m3u_data);
            foreach ($lines as $line) {
                if (empty($line) || $line[0] == '#') continue;
                $final .=  file_get_contents($line);
            }
            return $final;
        }
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
    public function url(string $url)
    {
        $run = true;
        $url_info = parse_url($url);
        $url_info['host'] = str_replace("www.", "", strtolower($url_info['host']));
        if ($url_info['host'] != 'soundcloud.com') {
            $url = $this->resolveShortUrl($url);
            $url_info = parse_url($url);
            $url_info['host'] = str_replace("www.", "", strtolower($url_info['host']));
            if ($url_info['host'] != 'soundcloud.com') $run = false;
            else $url = "https://" . $url_info['host'] . $url_info['path'];
        }
        if ($run) {
            $data = file_get_contents($url);
            $x = strpos($data, "__sc_hydration =") + strlen("__sc_hydration =");
            $data = substr($data, $x, strpos($data, ";</", $x) - $x);
            $xdata = json_decode($data, true);
            $data = [];
            foreach ($xdata as $k => $v) if (in_array($v['hydratable'], ['sound', 'user', 'playlist']))  $data[($v['hydratable'] == 'sound') ? 'track' : $v['hydratable']] = $v;
            $scopes = [];
            foreach ($data as $v) {
                $scopes[$v['hydratable']] = 1;
                if ($v['hydratable'] == 'sound') {
                    $dl = $this->findDownloadLink($v['data']);
                    if ($dl != false) $data['download'] = $dl;
                }
            }
            if (isset($scopes['sound'])) $data['type'] = 'track';
            else if (isset($scopes['playlist'])) $data['type'] = 'playlist';
            else if (isset($scopes['user'])) $data['type'] = 'user';
            return $data;
        } else return [];
    }
    public function track(string $id): array
    {
        if (!$this->client_id) $this->clientId();
        $data = ['track' => $this->get("https://api-v2.soundcloud.com/tracks", ['client_id' => $this->client_id, 'ids' => $id])[0]];
        $dl = $this->findDownloadLink($data['track']);
        if ($dl != false) $data['download'] = $dl;
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
