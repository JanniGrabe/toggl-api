<?php

namespace MorningTrain\TogglApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * Wrapper for the Toggl Api.
 *
 * @see https://github.com/toggl/toggl_api_docs/blob/master/toggl_api.md
 */
class TogglWebhooksApi {
    /**
     * @var string
     */
    protected $apiToken = '';

    /**
     * @var string
     */
    protected $workspaceId = '';

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * TogglApi constructor.
     *
     * @param string $apiToken
     */
    public function __construct($apiToken, $workspaceId) {
        $this->apiToken = $apiToken;
        $this->workspaceId = $workspaceId;
        $this->client = new Client([
            'base_uri' => 'https://track.toggl.com/webhooks/api/v1',
            'auth' => [$this->apiToken, 'api_token'],
        ]);
    }

    /**
     * Get available subscriptions.
     *
     * @return bool|mixed|object
     */
    public function getAvailableSubscriptions() {
        return $this->get('subscriptions/' . $this->workspaceId);
    }

    /**
     * Create subscription.
     *
     * @param array $subscription
     *
     * Subscription has the following properties
     *
     * - url_callback: The url toggl should call on event (string, required)
     * - event_filters: filter events by entity and action (array, required)
     * - enabled: enable subscription (boolean, required)
     * - description: description of the subscription (not required)
     *
     * @return bool|mixed|object
     *
     * @see https://github.com/toggl/toggl_api_docs/blob/master/webhooks.md#create-a-subscription
     */
    public function createSubscription($subscription) {
        return $this->POST('subscriptions/' . $this->workspaceId, ['subscription' => $subscription]);
    }

    /**
     * Update subscription.
     *
     * @param int $subscriptionId
     * @param array $subscription
     *
     * @return bool|mixed|object
     */
    public function updateSubscription($subscriptionId, $subscription) {
        return $this->PUT('subscriptions/' . $this->workspaceId . '/' . $subscriptionId, ['subscription' => $subscription]);
    }

    /**
     * Delete subscription.
     *
     * @param int $subscriptionId
     *
     * @return bool|mixed|object
     */
    public function deleteSubscription($subscriptionId) {
        return $this->DELETE('subscriptions/' . $this->workspaceId . '/' . $subscriptionId);
    }

    /**
     * Ping a subscription
     *
     * @param int $subscriptionId
     *
     * @return bool|mixed|object
     */
    public function pingSubscription($subscriptionId) {
        return $this->GET('ping/' . $this->workspaceId . '/' . $subscriptionId);
    }

    /**
     * Helper for client get command.
     *
     * @param string $endpoint
     * @param array $body
     * @param array $query
     *
     * @return bool|mixed|object
     */
    private function GET($endpoint, $query = array()) {
        try {
            $response = $this->client->get($endpoint, ['query' => $query]);

            return $this->checkResponse($response);
        } catch (ClientException $e) {
            return (object)[
                'success' => false,
                'message' => $e->getResponse()->getBody()->getContents(),
            ];
        }
    }

    /**
     * Wrapper for client post command.
     *
     * @param string $endpoint
     * @param array $body
     * @param array $query
     *
     * @return bool|mixed|object
     */
    private function POST($endpoint, $body = array(), $query = array()) {
        try {
            $response = $this->client->post($endpoint, ['body' => json_encode($body), 'query' => $query]);

            return $this->checkResponse($response);
        } catch (ClientException $e) {
            return (object)[
                'success' => false,
                'message' => $e->getResponse()->getBody()->getContents(),
            ];
        }
    }

    /**
     * Helper for client put command.
     *
     * @param string $endpoint
     * @param array $body
     * @param array $query
     *
     * @return bool|mixed|object
     */
    private function PUT($endpoint, $body = array(), $query = array()) {
        try {
            $response = $this->client->put($endpoint, ['body' => json_encode($body), 'query' => $query]);

            return $this->checkResponse($response);
        } catch (ClientException $e) {
            return (object)[
                'success' => false,
                'message' => $e->getResponse()->getBody()->getContents(),
            ];
        }
    }

    /**
     * Helper for client delete command.
     *
     * @param $endpoint
     * @param array $body
     * @param array $query
     *
     * @return bool|mixed|object
     */
    private function DELETE($endpoint, $body = array(), $query = array()) {
        try {
            $response = $this->client->delete($endpoint, ['body' => json_encode($body), 'query' => $query]);

            return $this->checkResponse($response);
        } catch (ClientException $e) {
            return (object)[
                'success' => false,
                'message' => $e->getResponse()->getBody()->getContents(),
            ];
        }
    }

    /**
     * Helper for checking http response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return bool|mixed
     */
    private function checkResponse($response) {
        if ($response->getStatusCode() == 200) {
            $data = json_decode($response->getBody());
            if (is_object($data) && isset($data->data)) {
                $data = $data->data;
            }

            return $data;
        }

        return false;
    }
}
