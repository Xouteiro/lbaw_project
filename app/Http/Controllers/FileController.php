<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class FileController extends Controller
{   
    static $default = 'default.jpg';
    static $diskName = 'Files'; 

    static $systemTypes = [
        'profile' => ['png', 'jpg', 'jpeg', 'gif'],
        'event' => ['mp3', 'mp4', 'gif', 'png', 'jpg', 'jpeg'],
    ];

    private static function getDefaultExtension(String $type) {
        return reset(self::$systemTypes[$type]);
    }

    private static function isValidExtension(String $type, String $extension) {
        $allowedExtensions = self::$systemTypes[$type];

        return in_array(strtolower($extension), $allowedExtensions);
    }

    private static function isValidType(String $type) {
        return array_key_exists($type, self::$systemTypes);
    }

    private static function defaultAsset(String $type) {
        return asset($type . '/' . self::$default);
    }

    private static function getFileName(String $type, int $id, String $extension = null) {

        $fileName = null;
        switch($type) {
            case 'profile':
                $fileName = User::find($id)->profile_image; // can be null as well
                break;
            case 'event':
                $fileName =  Event::find($id)->event_image;
                break;
            default:
                return null;
        }

        return $fileName;
    }

    private static function delete(String $type, int $id) {
        $existingFileName = self::getFileName($type, $id);
        if ($existingFileName) {
            Storage::disk(self::$diskName)->delete($type . '/' . $existingFileName);

            switch($type) {
                case 'profile':
                    $user = User::find($id);
                    $user->profile_image = null;
                    $user->save();
                    break;
                case 'event':
                    $event = Event::find($id);
                    $event->event_image = null;
                    $event->save();
                    break;
            }
        }
    }

    public function upload(Request $request) {

        if (!$request->hasFile('file')) {
            return redirect()->back()->with('error', 'Error: File not found');
        }

        if (!$this->isValidType($request->type)) {
            return redirect()->back()->with('error', 'Error: Unsupported upload type');
        }

        $file = $request->file('file');
        $type = $request->type;
        $extension = $file->extension();
        if (!$this->isValidExtension($type, $extension)) {
            return redirect()->back()->with('error', 'Error: Unsupported upload extension');
        }

        // Prevent existing old files
        $this->delete($type, $request->id);

        // Generate unique filename
        $fileName = $file->hashName();

        // Validation: model
        $error = null;
        switch($request->type) {
            case 'profile':
                $user = User::findOrFail($request->id);
                if ($user) {
                    $user->profile_image = $fileName;
                    $user->save();
                } else {
                    $error = "unknown user";
                }
                break;

            case 'event':
                $event = Event::findOrFail($request->id);
                if ($event) {
                    $event->event_image = $fileName;
                    $event->save();
                } else {
                    $error = "unknown event";
                }
                break;

            default:
                redirect()->back()->with('error', 'Error: Unsupported upload object');
        }

        if ($error) {
            redirect()->back()->with('error', `Error: {$error}`);
        }

        $file->storeAs($type, $fileName, self::$diskName);
        if($type == 'profile'){
            return redirect()->route('user.show', ['id' => $request->id])->with('success', 'Success: upload completed!');
        }else{
            return redirect()->route('event.show', ['id' => $request->id])->with('success', 'Success: upload completed!');
        }
    }

    static function get(String $type, int $userId) {

        // Validation: upload type
        if (!self::isValidType($type)) {
            return self::defaultAsset($type);
        }

        // Validation: file exists
        $fileName = self::getFileName($type, $userId);
        if ($fileName) {
            return asset($type . '/' . $fileName);
        }

        // Not found: returns default asset
        return self::defaultAsset($type);
    }

    public function deleteProfilePicture(Request $request) {
        $user = User::find($request->id);
        $auth = Auth::user();
        $this->authorize('deleteProfilePicture',$auth, $user);
        $userId = $request->id;
        $this->delete('profile', $userId);
        return redirect()->back()->with('success', 'Success: profile picture deleted!');
    }

    public function deleteEventPicture(Request $request) {
        $event = Event::find($request->id);
        $owner = User::find($event->id_owner);
        $auth = Auth::user();
        $this->authorize('deleteEventPicture',$auth, $owner);
        $eventId = $request->id;
        $this->delete('event', $eventId);
        return redirect()->back()->with('success', 'Success: profile picture deleted!');
    }

}
