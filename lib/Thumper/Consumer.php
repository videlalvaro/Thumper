<?php
/**
 * The MIT License
 *
 * Copyright (c) 2010 Alvaro Videla
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 *
 * @category   Thumper
 * @package    Thumper
 */
namespace Thumper;
use Thumper\BaseConsumer;
use PhpAmqpLib\Message\AMQPMessage;
use Exception;

/**
 *
 *
 *
 * @category   Thumper
 * @package    Thumper
 */
class Consumer extends BaseConsumer
{

    /**
     * @var int
     */
    public $consumed = 0;

    /**
     * @param int $msgAmount
     */
    public function consume($msgAmount)
    {
        $this->target = $msgAmount;

        $this->setUpConsumer();

        while (count($this->ch->callbacks)) {
            $this->ch->wait();
        }
    }

    /**
     * @param $msg
     *
     * @throws \Exception
     */
    public function processMessage($msg)
    {
        try {
            call_user_func($this->callback, $msg->body);
            $msg->delivery_info[ 'channel' ]->basic_ack(
                $msg->delivery_info[ 'delivery_tag' ]
            );
            $this->consumed++;
            $this->maybeStopConsumer($msg);
        }
        catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param AMQPMessage $msg
     */
    protected function maybeStopConsumer(AMQPMessage $msg)
    {
        if ($this->consumed == $this->target) {
            $msg->delivery_info[ 'channel' ]->basic_cancel(
                $msg->delivery_info[ 'consumer_tag' ]
            );
        }
    }
}
