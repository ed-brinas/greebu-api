<?php

namespace App\Http\Controllers;

use App\Models\ArtistType;
use App\Models\Duration;
use App\Models\Genre;
use App\Models\Purpose;
use App\Models\SongRequest;
use App\Models\SongType;
use App\Models\SupportedLanguage;

use Carbon\Carbon;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class SongController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:customers'])->only([
            'create', 'store',
            'edit', 'update',
            'updateApprovalStatus',
        ]);

        $this->middleware(['role:artists'])->only([
            'updateRequestStatus',
        ]);

        $this->middleware(['role:super-admin'])->only([
            'updateVerificationStatus',
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function create()
    {

        return response()->json([
            'status'    => 200,
            'message'   => 'Song Request form data fetched successfully.',
            'result'    => [
                'artist_types'  => ArtistType::all(),
                'mood'          => SongType::all(),
                'languages'     => SupportedLanguage::all(),
                'durations'     => Duration::all(),
                'purposes'      => Purpose::all(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'artist_type_id'    => ['required', 'exists:artist_types,id',],
            'artist_id'         => ['required', 'exists:artists,id',],
            'genre_id'          => ['required', 'exists:genres,id',],
            'song_type_id'      => ['required', 'exists:song_types',], // mood
            'language_id'       => ['required', '',], // supported_languages
            'duration_id'       => ['required', '',], // durations
            'purpose_id'        => ['required', '',], // purposes
            'first_name'        => ['required', 'string', 'max:255',],
            'last_name'         => ['required', 'string', 'max:255',],
            'email'             => ['required', 'email:rfc,dns', 'max:255',],
            'sender'            => ['required', 'string', 'max:255',],
            'receiver'          => ['required', 'string', 'max:255',],
            'user_story'        => ['required', 'string', 'max:500',],
            'page_status'       => ['required', 'string', 'max:64'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => "Invalid data",
                'result' => [
                    'errors' => $validator->errors(),
                ],
            ], 203);
        }

        $songs = SongRequest::create([
            'creator_id'        => auth()->user()->id,
            'artist_type_id'    => $request->artist_type_id,
            'genre_id'          => $request->genre_id,
            'song_type_id'      => $request->song_type_id,
            'language_id'       => $request->language_id,
            'duration_id'       => $request->duration_id,
            'purpose_id'        => $request->purpose_id,
            'first_name'        => $request->first_name,
            'last_name'         => $request->last_name,
            'email'             => $request->email,
            'sender'            => $request->sender,
            'receiver'          => $request->receiver,
            'user_story'        => $request->user_story,
            'request_status'    => 'pending',
            'page_status'       => $request->page_status,
        ]);

        $this->successResponse('...', [
            'song_request' => $songs,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(SongRequest $songRequest)
    {
        //
        return response()->json([
            'status' => 200,
            'message'   => '...',
            'result'    => [
                'song_request' => $songRequest,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SongRequest $songRequest)
    {
        //
        $validator = Validator::make($request->all(), [
            'artist_type_id'    => ['required', 'exists:artist_types,id',],
            'genre_id'          => ['required', 'exists:genres,id',],
            'song_type_id'      => ['required', 'exists:song_types',], // mood
            'language_id'       => ['required', '',], // supported_languages
            'duration_id'       => ['required', '',], // durations
            'purpose_id'        => ['required', '',], // purposes
            'first_name'        => ['required', 'string', 'max:255',],
            'last_name'         => ['required', 'string', 'max:255',],
            'email'             => ['required', 'email:rfc,dns', 'max:255',],
            'sender'            => ['required', 'string', 'max:255',],
            'receiver'          => ['required', 'string', 'max:255',],
            'user_story'        => ['required', 'string', 'max:500',],
            'page_status'       => ['required', 'string', 'max:64'],
            'estimate_date'     => ['required', 'integer',],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => "Invalid data",
                'result' => [
                    'errors' => $validator->errors(),
                ],
            ], 203);
        }

        $songRequest;

        $songRequest->artist_type_id = $request->input('artist_type_id');
        $songRequest->genre_id = $request->input('genre_id');
        $songRequest->song_type_id = $request->input('song_type_id');
        $songRequest->language_id = $request->input('language_id');
        $songRequest->duration_id = $request->input('duration_id');
        $songRequest->purpose_id = $request->input('purpose_id');
        $songRequest->first_name = $request->input('first_name');
        $songRequest->last_name = $request->input('last_name');
        $songRequest->email = $request->input('email');
        $songRequest->sender = $request->input('sender');
        $songRequest->receiver = $request->input('receiver');
        $songRequest->user_story = $request->input('user_story');
        $songRequest->page_status = $request->input('page_status');
        $songRequest->estimate_date = $request->input('estimate_date', 3);
        $songRequest->save();

        $this->successResponse('...', [
            'song_request' => $songRequest,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SongRequest $songRequest)
    {
        //
    }

    // Artist
    public function updateRequestStatus(Request $request, SongRequest $songRequest)
    {

        $validator = Validator::make($request->all(), [
            'request_status'    => ['required', 'in:pending,accepted,declined',],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => "Invalid data",
                'result' => [
                    'errors' => $validator->errors(),
                ],
            ], 203);
        }

        $status = $request->input('request_status', 'pending');
        $songRequest->request_status = $status;

        if ($status === 'accepted') {
            $songRequest->delivery_date  = now()->addDays($songRequest->estimate_date);
        }

        $songRequest->save();

        $this->successResponse('...', [
            'song_request' => $songRequest,
        ]);
    }


    // Customer
    public function updateApprovalStatus(Request $request, SongRequest $songRequest)
    {

        $validator = Validator::make($request->all(), [
            'request_status'    => ['required', 'in:pending,accepted,declined',],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => "Invalid data",
                'result' => [
                    'errors' => $validator->errors(),
                ],
            ], 203);
        }

        $songRequest->approval_status = $request->input('approval_status', 'inspecting');

        if ($request->approval_status === 'accepted') {
            $songRequest->approved_at = now();
        }

        $songRequest->save();

        $this->successResponse('...', [
            'song_request' => $songRequest,
        ]);
    }

    public function updateVerificationStatus(Request $request, SongRequest $songRequest)
    {
        $validator = Validator::make($request->all(), [
            'verification_status'    => ['required', 'boolean',],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => "Invalid data",
                'result' => [
                    'errors' => $validator->errors(),
                ],
            ], 203);
        }

        $songRequest->verification_status = $request->input('verification_status', false);

        $songRequest->save();

        $this->successResponse('...', [
            'song_request' => $songRequest,
        ]);
    }
}