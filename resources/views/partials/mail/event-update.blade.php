<?php
    use Illuminate\Support\Facades\DB;
    use App\Models\Notification;

    $notif = Notification::findOrFail($eventUpdateId);
    $eventLink = $notif->link . '?id_eventUpdate=' . $notif->id;
?>

<h3>Hello, {{ $name }}</h3>
<h3>The event @if(isset($whatChanged['old_name'])) {{ $whatChanged['old_name'] }} @else {{ $event }} @endif has been updated!</h3>
<h3>New values of the information that has changed:</h3>
<ul>
    @foreach($whatChanged as $key => $change)
        @if($key == 'id_location')
            <li>
                <?php 
                    $locationOld = DB::table('location')->where('id', $whatChanged['old_id_location'])->first();
                    $locationNew = DB::table('location')->where('id', $change)->first();
                ?>
                <strong>Location:</strong>
                <ul style="list-style-type: none;">
                    <li>
                        <strong>Old: {{ $locationOld->name }}</strong>
                    </li>
                    <li>
                        <strong>New: {{ $locationNew->name }}</strong>
                    </li>
                </ul>
            </li>
        @elseif($key == 'eventdate')
            <li>
                <strong>Date:</strong>
                <ul style="list-style-type: none;">
                    <li>
                        <strong>Old: {{ date('d-m-Y H:i', strtotime($whatChanged['old_eventdate'])) }}</strong>
                    </li>
                    <li>
                        <strong>New: {{ date('d-m-Y H:i', strtotime($change)) }}</strong>
                    </li>
                </ul>
            </li>
        @elseif($key == 'description')
            <li>
                <strong>Description:</strong>
                <ul style="list-style-type: none;">
                    <li>
                        <strong>Old: {{ $whatChanged['old_description'] }}</strong>
                    </li>
                    <li>
                        <strong>New: {{ $change }}</strong>
                    </li>
                </ul>
            </li>
        @elseif($key == 'name')
            <li>
                <strong>Name:</strong>
                <ul style="list-style-type: none;">
                    <li>
                        <strong>Old: {{ $whatChanged['old_name'] }}</strong>
                    </li>
                    <li>
                        <strong>New: {{ $change }}</strong>
                    </li>
                </ul>
            </li>
        @elseif($key == 'price')
            <li>
                <strong>Price:</strong>
                <ul style="list-style-type: none;">
                    <li>
                        <strong>Old: {{ $whatChanged['old_price'] }}</strong>
                    </li>
                    <li>
                        <strong>New: {{ $change }}</strong>
                    </li>
                </ul>
            </li>
        @elseif($key == 'capacity')
            <li>
                <strong>Capacity:</strong>
                <ul style="list-style-type: none;">
                    <li>
                        <strong>Old: {{ $whatChanged['old_capacity'] }}</strong>
                    </li>
                    <li>
                        <strong>New: {{ $change }}</strong>
                    </li>
                </ul>
            </li>
        @elseif($key == 'public')
            <li>
                <strong>Privacy:</strong>
                <ul style="list-style-type: none;">
                    <li>
                        <strong>Old: @if($whatChanged['old_public'])Public @else Private @endif</strong>
                    </li>
                    <li>
                        <strong>New: @if($change)Public @else Private @endif</strong>
                    </li>
                </ul>
            </li>
        @elseif($key == 'opentojoin')
            <li>
                <strong>Open to join:</strong>
                <ul style="list-style-type: none;">
                    <li>
                        <strong>Old: @if($whatChanged['old_opentojoin'])Yes @else No @endif</strong>
                    </li>
                    <li>
                        <strong>New: @if($change)Yes @else No @endif</strong>
                    </li>
                </ul>
            </li>
        @endif
    @endforeach
</ul>
<h3>Check out the updated event <a href="{{ url($eventLink) }}">here</a></h3>
<h4>Best regards,</h4>
<h4>Invents Staff</h4>