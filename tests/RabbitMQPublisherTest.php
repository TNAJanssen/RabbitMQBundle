<?php

namespace SimpleBus\RabbitMQBundle\Tests;

use SimpleBus\Message\Message;
use SimpleBus\RabbitMQBundle\RabbitMQPublisher;

class RabbitMQPublisherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_serializes_the_message_and_publishes_it_using_the_resolved_router_key()
    {
        $message = $this->dummyMessage();
        $routingKey = 'the-routing-key';
        $serializedMessageEnvelope = 'the-serialized-message-envelope';
        $serializer = $this->mockSerializer();
        $serializer
            ->expects($this->once())
            ->method('wrapAndSerialize')
            ->with($message)
            ->will($this->returnValue($serializedMessageEnvelope));

        $producer = $this->mockProducer();
        $producer
            ->expects($this->once())
            ->method('publish')
            ->with($this->identicalTo($serializedMessageEnvelope), $this->identicalTo($routingKey));

        $routingKeyResolver = $this->routingKeyResolverStub($message, $routingKey);

        $publisher = new RabbitMQPublisher($serializer, $producer, $routingKeyResolver);

        $publisher->publish($message);
    }

    private function mockSerializer()
    {
        return $this->getMock('SimpleBus\Serialization\Envelope\Serializer\MessageInEnvelopSerializer');
    }

    private function mockProducer()
    {
        return $this
            ->getMockBuilder('OldSound\RabbitMqBundle\RabbitMq\Producer')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function dummyMessage()
    {
        return $this->getMock('SimpleBus\Message\Message');
    }

    private function routingKeyResolverStub(Message $message, $routingKey)
    {
        $resolver = $this->getMock('SimpleBus\RabbitMQBundle\Routing\RoutingKeyResolver');
        $resolver
            ->expects($this->any())
            ->method('resolveRoutingKeyFor')
            ->with($this->identicalTo($message))
            ->will($this->returnValue($routingKey));

        return $resolver;
    }
}
