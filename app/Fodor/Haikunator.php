<?php namespace App\Fodor;

class Haikunator
{
// Some borrowed with love from http://www.enchantedlearning.com/wordlist/adjectives.shtml
    public static $ADJECTIVES = [
        "able", "admired", "adorable", "adventurous", "acclaimed", "accurate", "aged", "ancient", "aromatic", "autumn",
        "beautiful", "billowing", "bitter", "black", "blue", "bold", "brave", "broad", "broken", "bubbly",
        "calm", "cold", "cool", "crimson", "curly",
        "damp", "dark", "dawn", "delicate", "divine", "dry", "dry",
        "eager", "early", "easy", "edible", "empty", "educated", "enormous", "equal", "essential", "evil",
        "falling", "fancy", "flat", "floral", "fragrant", "free", "frosty",
        "gargantuan", "gifted", "giant", "giddy", "gliterring", "graceful", "grand", "granular", "groovy", "gentle", "green",
        "hairy", "half", "handmade", "handsome", "handy", "happy", "harmless", "hasty", "healthy", "hidden", "holy", "hushy",
        "icy", "ideal", "illustrious", "impeccable", "impressive", "improbable", "incomparable", "intrepid",
        "jagged", "jaunty", "jovial", "jolly", "joyous", "joyful", "juicy", "junior", "jumbo",
        "late", "lingering", "little", "lively", "long", "lucky",
        "misty", "morning", "muddy", "mute",
        "nameless", "neat", "next", "nocturnal", "noteworthy", "narrow", "near", "nippy", "noisy",
        "odd", "old", "orange",
        "patient", "plain", "polished", "proud", "purple",
        "quaint", "quick", "quiet", "quixotic",
        "rapid", "raspy", "red", "restless", "rough", "round", "royal",
        "shinny", "shrill", "shy", "silent", "small", "snowy", "soft", "solitary", "sparkling",
        "spring", "square", "steep", "still", "summer", "super", "sweet",
        "throbbing", "tight", "tiny", "twilight",
        "wandering", "weathered", "white", "wild", "windy", "winter", "wispy", "withered",
        "yawning", "yellow", "yellowish", "young", "youthful", "yummy",
        "zany", "zealous", "zesty", "zigzag"
    ];

    public static $NOUNS = [
        "waterfall", "river", "breeze", "moon", "rain", "wind", "sea", "morning",
        "snow", "lake", "sunset", "pine", "shadow", "leaf", "dawn", "glitter",
        "forest", "hill", "cloud", "meadow", "sun", "glade", "bird", "brook",
        "butterfly", "bush", "dew", "dust", "field", "fire", "flower", "firefly",
        "feather", "grass", "haze", "mountain", "night", "pond", "darkness",
        "snowflake", "silence", "sound", "sky", "shape", "surf", "thunder",
        "violet", "water", "wildflower", "wave", "water", "resonance", "sun",
        "wood", "dream", "cherry", "tree", "fog", "frost", "voice", "paper",
        "frog", "smoke", "star", "atom", "band", "bar", "base", "block", "boat",
        "term", "credit", "art", "fashion", "truth", "disk", "math", "unit", "cell",
        "scene", "heart", "recipe", "union", "limit", "bread", "toast", "bonus",
        "lab", "mud", "mode", "poetry", "tooth", "hall", "king", "queen", "lion", "tiger",
        "penguin", "kiwi", "cake", "mouse", "rice", "coke", "hola", "salad", "hat",
        "ace", "autumn", "animation", "backbone", "Balloon", "beach", "blockbuster", "bloom", "blossom", "brain", "branch",
        "bells", "brook", "bubbles", "butterfly", "cake", "candy", "candle", "castle", "chocolate", "coffee", "cub",
        "carrot", "throne", "ruby", "stream", "summit", "sun", "tuxedo", "puppy", "story", "video"
    ];

    /**
     * Generate Heroku-like random names to use in your applications.
     * @param array $params
     * @return string
     */
    public static function haikunate(array $params = array())
    {
        $defaults = [
            "delimiter" => "-",
            "tokenLength" => 4,
            "tokenHex" => false,
            "tokenChars" => "0123456789",
        ];

        $params = array_merge($defaults, $params);

        if ($params["tokenHex"] == true) {
            $params["tokenChars"] = "0123456789abcdef";
        }

        $adjective = self::$ADJECTIVES[mt_rand(0, count(self::$ADJECTIVES) - 1)];
        $noun = self::$NOUNS[mt_rand(0, count(self::$NOUNS) - 1)];
        $token = "";

        for ($i = 0; $i < $params["tokenLength"]; $i++) {
            $token .= $params["tokenChars"][mt_rand(0, strlen($params["tokenChars"]) - 1)];
        }

        $sections = [$adjective, $noun, $token];
        return implode($params["delimiter"], array_filter($sections));
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function __invoke(array $params = [])
    {
        return static::haikunate($params);
    }
}