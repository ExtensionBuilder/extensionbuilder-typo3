<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Tools;

class Uri
{

    protected string $scheme;
    protected string $user;
    protected string $pass;
    protected string $host;
    protected string $port;
    protected string $path;
    protected string $query;
    protected string $fragment;

    protected bool $online = false;
    protected bool $onlineV4 = false;
    protected bool $onlineV6 = false;

    protected int $count = 1;
    protected int $timeout = 5;

    protected string $hostIpV4 = '';
    protected string $hostIpV6 = '';

    protected array $pingV4 = [];
    protected array $pingV6 = [];


    protected int $socketErrNo = 0;
    protected string $socketErrStr = '';

    protected array $traceRoute = [];

    final function __construct(
        protected readonly string $uri,
    ) {
        if (!(stripos($uri, '://') ?? false)) {
            if ($positionAt = stripos($uri, '@')) {
                if ($positionColon = stripos($uri, ':')) {
                     $this->user = substr($uri, 0 ,$positionColon);
                     $this->pass = substr($uri, $positionColon + 1, $positionAt - $positionColon - 1);
                }
                $uri = substr($uri, $positionAt + 1);
			}
            $parts = parse_url($uri);
        } else {
            $parts = parse_url($uri);

            $this->user = $parts['user'] ?? '';
            $this->pass = $parts['pass'] ?? '';
        }

        $this->scheme = $parts['scheme'] ?? '';
        $this->host = $parts['host'] ?? '';
        $this->port = (string)($parts['port'] ?? '');
        $this->path = $parts['path'] ?? '';
        $this->query = $parts['query'] ?? '';
        $this->fragment = $parts['fragment'] ?? '';

        if (!(strpos($this->host, '[') === false)) {
            $this->host = substr($this->host, 1);
            $this->host = substr($this->host, 0, strlen($this->host) - 1);
        }

        if (filter_var($this->host, FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)) {
            $this->hostIpV4 = $this->host;
        } elseif (filter_var($this->host, FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)) {
            $this->hostIpV6 = $this->host;
        } else {
            $dnsGetA = dns_get_record($this->host, DNS_A);
            $dnsGetAAAA = dns_get_record($this->host, DNS_AAAA);
            $this->hostIpV4 = $dnsGetA[0]['ip'] ?? '';
            $this->hostIpV6 = $dnsGetAAAA[0]['ipv6']?? '';
        }

        if (!($this->port)) {
            switch ($this->scheme) {
                case 'http':
                    $this->port = '80';
                    break;
                case 'https':
                    $this->port = '443';
                    break;
            }
        }

        if ($this->ping()) { $this->socketCheck(); };
	}

    final function isOnline(): bool
    {
        return $this->online;
	}

    final function getHost(): string
    {
        return $this->host;
	}



    final function ping(): bool
    {
        $ping = false;
        if ($this->hostIpV4) {
            $this->pingV4 = $this->execPing($this->hostIpV4);
            $this->onlineV4 = $this->pingV4['online'];
            $ping = true;
        }

        if ($this->hostIpV6) {
            $this->pingV6 = $this->execPing($this->hostIpV6);
            $this->onlineV6 = $this->pingV6['online'];
            $ping = true;
        }
		
        return $ping;
	}

    final function socketCheck(): bool {
        $socket = false;
        $this->isOnline = false;

        if ($this->port) {
            $host = '';
            if ($this->onlineV6) {
                $host = '[' . $this->hostIpV6 . ']';
			} else {
                $host = $this->hostIpV4;
			}

            $fsock = @fsockopen($host, (int)$this->port, $this->socketErrNo, $this->socketErrStr, 30);

            if ($this->socketErrNo === 0) {
                fclose($fsock);
                $this->online = true;
                $socket = true;
            }
		}

        return $socket;
	}

    private function execPing(
        string $host,
    ): array {
        exec(sprintf(
                'ping -c ' . $this->count . ' -W ' . $this->timeout . ' %s', escapeshellarg($host)),
                $result,
                $resultStatus,
        );

        $pingResult['online'] = false;
        if ($resultStatus === 0) {
            foreach ($result ?? [] as $resultData) {
                if (!(strpos($resultData,'transmitted') === false)) {
                    $explode = explode(', ', $resultData);
                    $pingResult['transmitted'] = (int)substr($explode[0], 0, strpos($explode[0], ' packets transmitted'));
                    $pingResult['received'] = (int)substr($explode[1], 0, strpos($explode[1], ' received'));
                    $pingResult['loss'] = (int)substr($explode[2], 0, strpos($explode[2], '% packet loss'));
// ToDo ??? put in relation to $this->count
                    if ($pingResult['loss'] < 50) {
                        $pingResult['online'] = true;
                    }
                }
                if (!(strpos($resultData,'rtt min/avg/max/mdev = ') === false)) {
                    $time = substr($resultData, strlen('rtt min/avg/max/mdev = '));
                    $time = substr($time, 0, strpos($time,' ms'));
                    $explode = explode('/', $time);

                    $pingResult['min'] = (float)$explode[0];
                    $pingResult['avg'] = (float)$explode[1];
                    $pingResult['max'] = (float)$explode[2];
                    $pingResult['mdev'] = (float)$explode[3];
                }
            }
        }

        return $pingResult;
	}

}