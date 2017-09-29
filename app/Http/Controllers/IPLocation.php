<?php

class IPLocation
{
    private static $ip = null;

    private static $fp = null;
    private static $offset = null;
    private static $index = null;

    private static $cached = [];

    public function __destruct()
    {
        if (self::$fp !== null) {
            fclose(self::$fp);
        }

        self::$ip = null;
        self::$fp = null;
        self::$offset = null;
        self::$index = null;
        self::$cached = [];
    }

    public static function find($ip)
    {
        if (empty($ip) === true) {
            return 'N/A';
        }

        $ipdot = explode('.', $ip);

        if (!self::checkIpRange($ip)) {
            return 'N/A';
        }

        if (isset(self::$cached[$ip]) === true) {
            return self::$cached[$ip];
        }

        if (self::$fp === null) {
            if (!self::init()) {
                return 'N/A';
            }
        }

        $ip2 = pack('N', ip2long($ip));

        $tmp_offset = (int)$ipdot[0] * 4;
        $start = unpack('Vlen', self::$index[$tmp_offset] .
            self::$index[$tmp_offset + 1] .
            self::$index[$tmp_offset + 2] .
            self::$index[$tmp_offset + 3]);

        $index_offset = $index_length = null;
        $max_comp_len = self::$offset['len'] - 1024 - 4;
        for ($start = $start['len'] * 8 + 1024; $start < $max_comp_len; $start += 8) {
            if (self::$index{$start} .
                self::$index{$start + 1} .
                self::$index{$start + 2} .
                self::$index{$start + 3} >= $ip2
            ) {
                $index_offset = unpack('Vlen', self::$index{$start + 4} .
                    self::$index{$start + 5} .
                    self::$index{$start + 6} . "\x0");
                $index_length = unpack('Clen', self::$index{$start + 7});

                break;
            }
        }

        if ($index_offset === null) {
            return 'N/A';
        }

        fseek(self::$fp, self::$offset['len'] + $index_offset['len'] - 1024);

        self::$cached[$ip] = explode("\t", fread(self::$fp, $index_length['len']));

        return self::$cached[$ip];

    }

    private static function checkIpRange($ip)
    {
        $ipdot = explode('.', $ip);

        if (count($ipdot) !== 4) {
            return false;
        }

        foreach ($ipdot as $dot) {
            if ($dot < 0 || $dot > 255) {
                return false;
            }
        }

        return true;
    }

    private static function init()
    {
        if (self::$fp === null) {
            self::$ip = new self();
            if (\Illuminate\Support\Facades\Config::get('app.locale') == 'en') {
                self::$fp = fopen(app_path('Vendor/17monipdb_en.dat'), 'rb');
            } else {
                self::$fp = fopen(app_path('Vendor/17monipdb.dat'), 'rb');
            }

            if (self::$fp === false) {
                return false;
            }

            self::$offset = unpack('Nlen', fread(self::$fp, 4));

            if (self::$offset['len'] < 4) {
                return false;
            }

            self::$index = fread(self::$fp, self::$offset['len'] - 4);
        }

        return true;
    }
}