<?php

namespace Dew\Tablestore;

use Dew\Tablestore\Concerns\CommunicatesWithAcs;
use Dew\Tablestore\Contracts\BuildsSignature;
use Dew\Tablestore\Middlewares\ConfigureMetadata;
use Dew\Tablestore\Middlewares\SignRequest;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\ResponseInterface;

class TablestoreInstance
{
    use CommunicatesWithAcs;

    /**
     * Create a Tablestore instance client.
     */
    public function __construct(
        protected string $accessKeyId,
        protected string $accessKeySecret,
        protected string $region
    ) {
        //
    }

    /**
     * The ACS region.
     */
    public function region(): string
    {
        return $this->region;
    }

    /**
     * The Tablestore federal endpoint.
     *
     * @see https://github.com/aliyun/terraform-provider-alicloud/blob/5ec9f55e1f5ee8352074e117752d434c96060f47/alicloud/connectivity/endpoint.go#L165
     */
    public function endpoint(): string
    {
        return sprintf('https://tablestore.%s.aliyuncs.com', $this->region);
    }

    /**
     * List instances by the given criteria.
     *
     * @param  array{status?: string, maxResults?: int, nextToken?: string}  $criteria
     */
    public function all(array $criteria = []): ResponseInterface
    {
        return $this->send('GET', '/v2/openapi/listinstances', [
            'query' => $criteria,
            'acs' => [
                'action' => 'ListInstances',
                'version' => '2020-12-09',
            ],
        ]);
    }

    /**
     * Get Tablestore instance information.
     */
    public function get(string $instance): ResponseInterface
    {
        return $this->send('GET', '/v2/openapi/getinstance', [
            'query' => [
                'InstanceName' => $instance,
            ],
            'acs' => [
                'action' => 'GetInstance',
                'version' => '2020-12-09',
            ],
        ]);
    }

    /**
     * Create a new Tablestore instance.
     *
     * @param  array{
     *   InstanceName: string,
     *   InstanceDescription?: string,
     *   AliasName?: string,
     *   ClusterType?: string,
     *   Network?: string
     * }  $instance
     */
    public function create(array $instance): ResponseInterface
    {
        return $this->send('POST', '/v2/openapi/createinstance', [
            'json' => $instance,
            'acs' => [
                'action' => 'CreateInstance',
                'version' => '2020-12-09',
            ],
        ]);
    }

    /**
     * Update an existing Tablestore instance.
     *
     * @param  array{
     *   InstanceName: string,
     *   InstanceDescription?: string,
     *   AliasName?: string,
     *   Network?: string
     * }  $instance
     */
    public function update(array $instance): ResponseInterface
    {
        return $this->send('POST', '/v2/openapi/updateinstance', [
            'json' => $instance,
            'acs' => [
                'action' => 'UpdateInstance',
                'version' => '2020-12-09',
            ],
        ]);
    }

    /**
     * Delete Tablestore instance.
     */
    public function delete(string $instance): ResponseInterface
    {
        return $this->send('POST', '/v2/openapi/deleteinstance', [
            'json' => [
                'InstanceName' => $instance,
            ],
            'acs' => [
                'action' => 'DeleteInstance',
                'version' => '2020-12-09',
            ],
        ]);
    }

    /**
     * Attach tags to the resources.
     *
     * @param  array{
     *   ResourceIds: string[],
     *   ResourceType: string,
     *   Tags: array{Key: string, Value: string}[]
     * }  $data
     */
    public function tag(array $data): ResponseInterface
    {
        return $this->send('POST', '/v2/openapi/tagresources', [
            'json' => $data,
            'acs' => [
                'action' => 'TagResources',
                'version' => '2020-12-09',
            ],
        ]);
    }

    /**
     * Attach tags to the Tablestore instances.
     *
     * @param  string|string[]  $instances
     * @param  array<string, string>  $tags
     */
    public function tagInstance(array|string $instances, array $tags): ResponseInterface
    {
        $build = [];

        foreach ($tags as $key => $value) {
            $build[] = ['Key' => $key, 'Value' => $value];
        }

        return $this->tag([
            'ResourceType' => 'INSTANCE',
            'ResourceIds' => is_array($instances) ? $instances : [$instances],
            'Tags' => $build,
        ]);
    }

    /**
     * Remove tags from the resources.
     *
     * @param  array{
     *   ResourceIds: string[],
     *   ResourceType: string,
     *   TagKeys: string[]
     * }  $data
     */
    public function untag(array $data): ResponseInterface
    {
        return $this->send('POST', '/v2/openapi/untagresources', [
            'json' => $data,
            'acs' => [
                'action' => 'UntagResources',
                'version' => '2020-12-09',
            ],
        ]);
    }

    /**
     * Remove tags from Tablestore instances.
     *
     * @param  string[]|string  $instances
     * @param  string[]|string  $tags
     */
    public function untagInstance(array|string $instances, array|string $tags): ResponseInterface
    {
        return $this->untag([
            'ResourceType' => 'INSTANCE',
            'ResourceIds' => is_array($instances) ? $instances : [$instances],
            'TagKeys' => is_array($tags) ? $tags : [$tags],
        ]);
    }

    /**
     * Send the HTTP request.
     *
     * @param  array<string, mixed>  $options
     */
    public function send(string $method, string $endpoint, array $options = []): ResponseInterface
    {
        $handler = HandlerStack::create();
        $handler->push(ConfigureMetadata::forAcs($this));
        $handler->push(SignRequest::acs($this->signature(), $this->accessKeyId));

        $client = new Client(array_merge($this->options(), [
            'base_uri' => $this->endpoint(),
            'handler' => $handler,
        ]));

        return $client->request($method, $endpoint, $options);
    }

    /**
     * Create a new signature builder.
     */
    protected function newSignature(): BuildsSignature
    {
        return (new AcsSignature($this->accessKeySecret))->include([
            'host', 'content-type',
        ]);
    }
}
