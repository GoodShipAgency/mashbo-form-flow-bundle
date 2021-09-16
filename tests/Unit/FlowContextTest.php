<?php

namespace Unit;

use Mashbo\FormFlowBundle\FlowContext;
use Mashbo\FormFlowBundle\FlowInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class FlowContextTest extends TestCase
{
    protected FlowContext $sut;
    private FlowInterface $flow;
    private FormInterface $form;

    public function setUp(): void
    {
        $this->flow = $this->createMock(FlowInterface::class);
        $this->form = $this->createMock(FormInterface::class);

        $this->sut = new FlowContext(
            $this->flow,
            'test_name',
            null,
            $this->form
        );
    }

    public function test_can_set_subject(): void
    {
        $subject = new \stdClass();
        $this->sut->setSubject($subject);

        $this->assertSame($subject, $this->sut->subject);
    }

    public function test_get_name(): void
    {
        $this->assertEquals("test_name", $this->sut->getName());
    }

    public function test_get_request_with_no_request_throws_exception(): void
    {
        $this->expectException(\LogicException::class);

        $this->sut->getRequest();
    }

    public function test_get_request(): void
    {
        $request = new Request();

        $this->sut = new FlowContext(
            $this->flow,
            'test_name',
            $request,
            $this->form
        );

        $this->assertSame($request, $this->sut->getRequest());
    }

    public function test_get_flow(): void
    {
        $this->assertSame($this->flow, $this->sut->getFlow());
    }
}