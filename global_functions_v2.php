<?php
if (!class_exists('dota2_webapi_v2')) {
    class dota2_webapi_v2
    {
        private $steamAPIKey = NULL;

        public function __construct($steamAPIKey)
        {
            if (empty($steamAPIKey)) {
                throw new RuntimeException('No Steam Key Provided!');
            } else {
                $this->steamAPIKey = $steamAPIKey;
            }
        }

        function GetGameItems($language = 'en')
        {
            $APIresult = curl('https://api.steampowered.com/IEconDOTA2_570/GetGameItems/v1/?key=' . $this->steamAPIKey . '&format=json&language=' . $language);

            $APIresult = !empty($APIresult)
                ? json_decode($APIresult, 1)
                : false;

            return $APIresult;
        }

        function GetMatchHistory($steamID32, $matchesRequested = 100)
        {
            $APIresult = curl('https://api.steampowered.com/IDOTA2Match_570/GetMatchHistory/v1/?key=' . $this->steamAPIKey . '&format=json&hero_id=&game_mode=&skill=&min_players=1&account_id=' . $steamID32 . '&league_id=&start_at_match_id=&matches_requested=' . $matchesRequested . '');

            if (empty($APIresult)) throw new Exception('API returned nothing');

            $APIresult = json_decode($APIresult, 1);

            if (!isset($APIresult['result']['status']) || $APIresult['result']['status'] != '1') throw new Exception('API returned abnormal status');
            if (!isset($APIresult['result']['total_results']) || $APIresult['result']['total_results'] <= 0) throw new Exception('No results returned for these parameters');
            if (!isset($APIresult['result']['matches']) || count($APIresult['result']['matches']) <= 0) throw new Exception('No results returned for these parameters');

            return $APIresult;
        }
    }
}