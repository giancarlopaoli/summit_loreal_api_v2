<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Music;
use App\Models\MusicVote;

class MusicController extends Controller
{
    //
    public function music_votes (Request $request) {

        $music = Music::selectRaw('song_name,(select count(*) from music_votes where music_votes.music_id = music.id) as votes')->whereRaw("(select count(*) from music_votes where music_votes.music_id = music.id)  > 0")->where('status','Pendiente')->orderByRaw("(select count(*) from music_votes where music_votes.music_id = music.id) desc")->get();

        return response()->json([
            'success' => true,
            'data' => [
                'music' => $music
            ]
        ]);
    }

    public function music_list (Request $request) {

        $music = Music::selectRaw('id,song_name,status,(select count(*) from music_votes where music_votes.music_id = music.id) as votes')->orderByRaw("(select count(*) from music_votes where music_votes.music_id = music.id) desc")->get();

        return response()->json([
            'success' => true,
            'data' => [
                'music' => $music
            ]
        ]);
    }

    public function desactivate_music (Request $request, Music $music) {

        $music->status = 'Tocada';
        $music->save();

        return response()->json([
            'success' => true,
            'data' => [
                'music' => $music
            ]
        ]);
    }
}
