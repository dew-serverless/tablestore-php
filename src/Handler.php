<?php

namespace Dew\Tablestore;

use Dew\Tablestore\Concerns\InteractsWithRequest;
use Dew\Tablestore\Responses\RowDecodableResponse;
use Google\Protobuf\Internal\Message;
use Protos\DeleteRowRequest;
use Protos\DeleteRowResponse;
use Protos\GetRowRequest;
use Protos\GetRowResponse;
use Protos\PutRowRequest;
use Protos\PutRowResponse;
use Protos\UpdateRowRequest;
use Protos\UpdateRowResponse;

class Handler
{
    use InteractsWithRequest;

    /**
     * Create a new handler.
     */
    public function __construct(
        protected Tablestore $tablestore
    ) {
        //
    }

    /**
     * Send the put row request to Tablestore.
     *
     * @return \Dew\Tablestore\Responses\RowDecodableResponse<\Protos\PutRowResponse>
     */
    public function putRow(Builder $builder): RowDecodableResponse
    {
        $request = new PutRowRequest;
        $request->setTableName($builder->getTable());
        $request->setRow($builder->row->getBuffer());
        $request->setCondition($this->toCondition($builder));
        $request->setReturnContent($this->toReturnContent($builder));

        $response = new PutRowResponse;
        $response->mergeFromString($this->send('/PutRow', $request));

        return new RowDecodableResponse($response);
    }

    /**
     * Send the update row request to Tablestore.
     *
     * @return \Dew\Tablestore\Responses\RowDecodableResponse<\Protos\UpdateRowResponse>
     */
    public function updateRow(Builder $builder): RowDecodableResponse
    {
        $request = new UpdateRowRequest;
        $request->setTableName($builder->getTable());
        $request->setRowChange($builder->row->getBuffer());
        $request->setCondition($this->toCondition($builder));

        $response = new UpdateRowResponse;
        $response->mergeFromString($this->send('/UpdateRow', $request));

        return new RowDecodableResponse($response);
    }

    /**
     * Send the delete row request to Tablestore.
     *
     * @return \Dew\Tablestore\Responses\RowDecodableResponse<\Protos\DeleteRowResponse>
     */
    public function deleteRow(Builder $builder): RowDecodableResponse
    {
        $request = new DeleteRowRequest;
        $request->setTableName($builder->getTable());
        $request->setPrimaryKey($builder->row->getBuffer());
        $request->setCondition($this->toCondition($builder));
        $request->setReturnContent($this->toReturnContent($builder));

        $response = new DeleteRowResponse;
        $response->mergeFromString($this->send('/DeleteRow', $request));

        return new RowDecodableResponse($response);
    }

    /**
     * Send the get row request to Tablestore.
     *
     * @return \Dew\Tablestore\Responses\RowDecodableResponse<\Protos\GetRowResponse>
     */
    public function getRow(Builder $builder): RowDecodableResponse
    {
        $request = new GetRowRequest;
        $request->setTableName($builder->getTable());
        $request->setPrimaryKey($builder->row->getBuffer());
        $request->setColumnsToGet($builder->selects);
        $request->setMaxVersions($builder->maxVersions);

        if ($this->shouldBuildFilter($builder)) {
            $request->setFilter($this->buildFilter($builder)->serializeToString());
        }

        $response = new GetRowResponse;
        $response->mergeFromString($this->send('/GetRow', $request));

        return new RowDecodableResponse($response);
    }

    /**
     * Communicate with Tablestore with the given message.
     */
    protected function send(string $endpoint, Message $message): string
    {
        return $this->tablestore->send($endpoint, $message)
            ->getBody()
            ->getContents();
    }

    /**
     * The tablestore client.
     */
    public function tablestore(): Tablestore
    {
        return $this->tablestore;
    }
}
