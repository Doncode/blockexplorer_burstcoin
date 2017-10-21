<?php
// Transaktionstypen
$conf['transactiontypes'] = 
        array(
        0 => 
            array(
                0 => 'Ordinary Payment'
                ), 
        1 => 
            array(
                0 => 'Arbitrary Message',
                1 => 'Alias Assignment', 
                2 => 'Poll Creation', 
                3 => 'Vote Casting', 
                4 => 'Hub Announcements', 
                5 => 'Account Info', 
                6 => 'Alias Transfer/Sale', 
                7 => 'Alias Buy'
                ),
        2 =>
            array(
                0 => 'Asset Issuance',
                1 => 'Asset Transfer',
                2 => 'Ask Order Placement',
                3 => 'Bid Order Placement',
                4 => 'Ask Order Cancellation',
                5 => 'Bid Order Cancellation'
            ),
        3 =>
            array(
                0 => 'Marketplace Listing',
                1 => 'Marketplace Removal',
                2 => 'Marketplace Price Change',
                3 => 'Marketplace Quantity Change',
                4 => 'Marketplace Purchase',
                5 => 'Marketplace Delivery',
                6 => 'Marketplace Feedback',
                7 => 'Marketplace Refund'
            ),
        4 => array(
                array(0 => 'balance_leasing')
            ),
        20 => array(
                0 => 'Reward Recipient Assignment'
            ),
        21 => array(
                0 => 'Escrow Creation',
                1 => 'Escrow Signing',
                2 => 'Escrow Result',
                3 => 'Subscription Subscribe',
                4 => 'Subscription Cancel',
                5 => 'Subscription Payment'
            ),
        22 => array(
                0 => 'AT Creation',
                1 => 'AT Payment'
            )
        );

// Pools
$conf['pools'] = array(
    array('name' => 'FastPool.info', 'addr' => '611266021200711189', 'url' => 'https://fastpool.info/'),
    array('name' => '100pb.online', 'addr' => '10028292625366045160', 'url' => 'http://100pb.online/'),
    array('name' => 'burst.ninja', 'addr' => '7979631613202555765', 'url' => 'http://burst.ninja/'),
    array('name' => 'BurstCoin.ml', 'addr' => '13210932244776097704','url' => 'http://pool.burstcoin.ml:8020/'),
    array('name' => 'Burstcoin.de', 'addr' => '15291186589713514299', 'url' => 'http://pool.burstcoin.de/'),
    array('name' => 'pool.poolofd32th.club', 'addr' => '2311656582822632451', 'url' => 'http://pool.poolofd32th.club/'),
    array('name' => 'xen.poolofd32th.club', 'addr' => '4736628939229308608', 'url' => 'http://xen.poolofd32th.club/'),
    array('name' => 'pool.burstcoin.ro', 'addr' => '15587859947385731145', 'url' => 'http://pool.burstcoin.ro/'),
);


// Surfbar
$conf['surfbar'] = array('burstPayed' => 0, 'burstEuro' => 0, 'burstEuroRate' => 0.000210);
