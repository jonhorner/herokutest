<?php

namespace App\Http\Middleware;

use Storage;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use JsonStreamingParser\Parser;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\StreamWrapper;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use JsonStreamingParser\Listener\InMemoryListener;

class SwgohHelp {

    const URL_BASE = 'https://api.swgoh.help';
    const AUTH_PATH = '/auth/signin';
    const ACCESS_TOKEN_KEY = 'access_token';

    const API_PLAYER = 'player';
    const API_UNITS = 'units';
    const API_GUILD = 'guild';
    const API_DATA = 'data';

    public const FULL_ROSTER = 'roster';
    public const FULL_UNITS = 'units';

    /**
     * The HTTP Client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    public $enums = false;
    public $lang = 'eng_us';

    private $clientID;
    private $clientSecret;
    private $token;

    public function getPlayer($allyCode, $projection = []) {
        $data = [
            "allycode" => $allyCode,
            "language" => $this->lang,
            "enums" => $this->enums,
            "project" => array_merge([
                "allyCode" => 1,
                "name" => 1,
                "level" => 1,
                "guildName" => 1,
                "stats" => 1,
                "arena" => 1,
                "updated" => 1,
                "roster" => [
                    "defId" => 1,
                    "rarity" => 1,
                    "level" => 1,
                    "gear" => 1,
                    "combatType" => 1,
                    "gp" => 1,
                    "skills" => 1,
                    "relic" => 1,
                ]
            ], $projection),
        ];

        return $this->callAPI(static::API_PLAYER, $data);
    }

    public function getUnits($allyCode, $mods = false, $projection = []) {
        $inner = [
            "starLevel" => 1,
            "level" => 1,
            "gearLevel" => 1,
            "gear" => 1,
            "zetas" => 1,
            "type" => 1,
            "gp" => 1,
        ];

        if ($mods) {
            $inner['mods'] = 1;
        }

        $data = [
            "allycode" => $allyCode,
            "mods" => $mods,
            "language" => $this->lang,
            "enums" => $this->enums,
            "project" => array_merge($inner, $projection),
        ];

        return $this->callAPI(static::API_UNITS, $data);
    }

    public function getMods($allyCode) {
        $data = [
            "mods" => 1,
        ];

        return $this->getUnits($allyCode, true, $data);
    }

    public function getGuild($allyCode, Callable $memberCallback=null, $fullRoster = false, $mods = false, $projection = []) {
        $rosterInner = [
            "defId" => 1,
            "rarity" => 1,
            "level" => 1,
            "gear" => 1,
            "combatType" => 1,
            "gp" => 1,
            "skills" => 1,
            "relic" => 1,
        ];
        if ($mods) {
            $rosterInner["mods"] = 1;
        }
        $data = [
            "allycode" => $allyCode,
            "roster" => $fullRoster == static::FULL_ROSTER,
            "units" => $fullRoster == static::FULL_UNITS,
            "mods" => $mods,
            "language" => $this->lang,
            "enums" => $this->enums,
            "project" => array_merge([
                "name" => 1,
                "desc" => 1,
                "members" => 1,
                "status" => 1,
                "bannerColor" => 1,
                "bannerLogo" => 1,
                "message" => 1,
                "gp" => 1,
                "roster" => [
                    "allyCode" => 1,
                ],
            ], $projection),
        ];

        $guilds = $this->callAPI(static::API_GUILD, $data);

        if ($fullRoster == static::FULL_ROSTER || $fullRoster == static::FULL_UNITS) {
            $guilds->each(function($guild) use ($fullRoster, $rosterInner, $memberCallback) {
                $guildAllyCodes = collect($guild['roster'])->pluck('allyCode')
                    ->chunk(17)
                    ->each(function($allyCodeChunk) use ($fullRoster, $rosterInner, $memberCallback) {
                        $playerData = [
                            "allycodes" => $allyCodeChunk->values()->toArray(),
                            "language" => $this->lang,
                            "enums" => $this->enums,
                            "project" => [
                                "allyCode" => 1,
                                "name" => 1,
                                "level" => 1,
                                "stats" => 1,
                                "roster" => $rosterInner,
                                "arena" => 1,
                                "updated" => 1,
                            ]
                        ];
                        $this->callAPI($fullRoster == static::FULL_ROSTER ? static::API_PLAYER : static::API_UNITS, $playerData, $memberCallback);
                    });
            });
        }

        return $guilds;
    }

    public function getUnitData($match = [], $projection = []) {
        $data = [
            "collection" => "unitsList",
            "language" => $this->lang,
            "enums" => $this->enums,
            "match" => array_merge([ "rarity" => 7 ], $match),
            "project" => array_merge([
                "baseId" => 1,
                "nameKey" => 1,
                "thumbnailName" => 1,
                "basePower" => 1,
                "descKey" => 1,
                "combatType" => 1,
                "forceAlignment" => 1,
                "combatType" => 1,
                "skillReferenceList" => [ "skillId" => 1 ],
            ], $projection),
        ];

        return $this->callAPI(static::API_DATA, $data)
            ->map(function($unit) {
                $unit['skillReferenceList'] = collect($unit['skillReferenceList'])->flatten();
                return $unit;
            });
    }

    public function getZetaData() {
        $zData = [
            "collection" => "skillList",
            "language" => $this->lang,
            "enums" => $this->enums,
            "project" => [
                "id" => 1,
                "abilityReference" => 1,
                "isZeta" => 1,
            ],
        ];
        $aData = [
            "collection" => "abilityList",
            "language" => $this->lang,
            "enums" => $this->enums,
            "project" => [
                "id" => 1,
                "type" => 1,
                "nameKey" => 1,
                "descriptiveTagList" => 1,
            ]
        ];

        $abilities = $this->callAPI(static::API_DATA, $aData)
            ->map(function($ability) {
                $ability['descriptiveTagList'] = collect($ability['descriptiveTagList'])->pluck('tag');
                return $ability;
            });

        $skills = $this->callAPI(static::API_DATA, $zData);

        return $skills->filter(function($skill) { return $skill['isZeta']; })
            ->map(function($skill) use ($abilities) {
                $ability = $abilities->firstWhere('id', $skill['abilityReference']);
                $class = (preg_match('/^(.+)skill_/', $skill['id'], $matches)) ? trim($matches[1]) : null;
                return [
                    'name' => $ability['nameKey'],
                    'id' => $skill['id'],
                    'class' => title_case($class),
                ];
            });
    }

    public function callAPI($api, $payload, Callable $memberCallback = null) {
        if (is_null($this->getToken())) {
            $this->setToken($this->getAccessToken());
        }

        try {
            $response = $this->getHttpClient()->post($this->buildAPIUrl($api), [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$this->getToken(),
                ],
                'json' => $payload,
            ]);
            $raw = $response->getBody();
            $response = null;
            unset($response);
            if (is_null($memberCallback)) {
                $listener = new InMemoryListener;
            } else {
                $listener = new GuildListener($memberCallback);
            }
            $parser = new Parser(StreamWrapper::getResource($raw), $listener);
            $parser->parse();

            return collect($listener->getJson());
        } catch (ClientException | ServerException $e) {
            $response = $e->getResponse();
            $body = json_decode($response->getBody(), true);

            if ($response->getStatusCode() == 401 || $body['code'] == 401 || $response->getStatusCode() == 503) {
                $this->setToken(null);
                $args = func_get_args();
                return call_user_func_array([$this, __METHOD__], $args);
            }

            throw $e;
        }
    }

    protected function getTokenUrl() {
        return static::URL_BASE . static::AUTH_PATH;
    }

    protected function getAccessToken() {

        // if( !empty(ClientInterface::VERSION) ){
        //     $version = ClientInterface::VERSION;
        // } else {
            $version = ClientInterface::MAJOR_VERSION;
        // }

        $postKey = (version_compare($version, '6') === 1) ? 'form_params' : 'body';

        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            $postKey => $this->getTokenFields(),
        ]);

        return json_decode($response->getBody(), true)[static::ACCESS_TOKEN_KEY];
    }

    protected function getTokenFields() {
        return [
            'client_id' => config('services.swgoh_help.client_id', 'abc'),
            'client_secret' => config('services.swgoh_help.client_id', '123'),
            'username' => config('services.swgoh_help.user'),
            'password' => config('services.swgoh_help.password'),
            'grant_type' => 'password',
        ];
    }

    protected function buildAPIUrl($path) {
        // return static::URL_BASE . '/swgoh' . str_start($path, '/');
        return static::URL_BASE . '/swgoh' . Str::start($path, '/');
    }

    /**
     * Get a instance of the Guzzle HTTP client.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new Client();
        }

        return $this->httpClient;
    }

    /**
     * Set the Guzzle HTTP client instance.
     *
     * @param  \GuzzleHttp\Client  $client
     * @return $this
     */
    public function setHttpClient(Client $client)
    {
        $this->httpClient = $client;

        return $this;
    }

    public function withEnums() {
        $this->enums = true;

        return $this;
    }

    public function withoutEnums() {
        $this->enums = false;

        return $this;
    }

    public function lang($lang) {
        $this->lang = $lang;

        return $this;
    }

    protected function getToken() {
        if (is_null($this->token) && Storage::disk('local')->exists('.__token')) {
            $this->token = Storage::disk('local')->get('.__token');
        }
         return $this->token;
    }

    protected function setToken($token) {
        $this->token = $token;
        if (is_null($token)) {
            Storage::disk('local')->delete('.__token');
        } else {
            Storage::disk('local')->put('.__token', $token);
        }
    }
}