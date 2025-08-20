<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPortfolio;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;

class UserPortfolioController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        try {
            $portfolios = UserPortfolio::where('user_id', auth()->id())->get();
            return $this->successResponse($portfolios, 'Portfolio list fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch portfolios', 500, $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title'        => 'required|string|max:255',
                'video_url'    => 'nullable|url',
                // 'video_upload' => 'nullable|url', 
                'image_url' => 'nullable|url',
                         ]);

            $portfolio = UserPortfolio::create([
                'user_id'      => auth()->id(),
                'title'        => $request->title,
                'video_url'    => $request->video_url,
                // 'video_upload' => $request->video_upload,
                'image_url' => $request->image_url,
            ]);

            return $this->successResponse($portfolio, 'Portfolio created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create portfolio', 500, $e->getMessage());
        }
    }

    public function show($id)
    {
        $portfolio = UserPortfolio::where('user_id', auth()->id())->find($id);

        if (!$portfolio) {
            return $this->errorResponse('Portfolio not found', 404);
        }

        return $this->successResponse($portfolio, 'Portfolio details fetched successfully');
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'title'        => 'required|string|max:255',
                'video_url'    => 'nullable|url',
                // 'video_upload' => 'nullable|string',
                'image_url' => 'nullable|url',
            ]);

            $portfolio = UserPortfolio::where('user_id', auth()->id())->find($id);

            if (!$portfolio) {
                return $this->errorResponse('Portfolio not found', 404);
            }

            $portfolio->update($request->only('title','video_url','video_upload','image_upload'));

            return $this->successResponse($portfolio, 'Portfolio updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update portfolio', 500, $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $portfolio = UserPortfolio::where('user_id', auth()->id())->find($id);

            if (!$portfolio) {
                return $this->errorResponse('Portfolio not found', 404);
            }

            $portfolio->delete();

            return $this->successResponse([], 'Portfolio deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete portfolio', 500, $e->getMessage());
        }
    }
}





    // public function store(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'title'        => [
    //                 'required',
    //                 'string',
    //                 'max:255',
    //                 Rule::unique('user_portfolios')->where(function ($query) {
    //                     return $query->where('user_id', auth()->id());
    //                 })
    //             ],
    //             'description'  => 'nullable|string|max:1000',
    //             'video_url'    => 'nullable|url|regex:/^(https?:\/\/)/',
    //             'video_upload' => 'nullable|string|regex:/\.(mp4|mov|avi)$/i',
    //             'image_upload' => 'nullable|string|regex:/\.(jpg|jpeg|png|gif)$/i',
    //         ]);

    //         $userId = auth()->id();

    //         // Condition 1: Max 50 portfolios
    //         $count = UserPortfolio::where('user_id', $userId)->count();
    //         if ($count >= 50) {
    //             return $this->errorResponse('You cannot add more than 50 portfolios', 422);
    //         }

    //         // Condition 2: At least one media (video_url, video_upload, image_upload) must exist
    //         if (!$request->video_url && !$request->video_upload && !$request->image_upload) {
    //             return $this->errorResponse('At least one media (video or image) is required', 422);
    //         }

    //         $portfolio = UserPortfolio::create([
    //             'user_id'      => $userId,
    //             'title'        => $request->title,
    //             'description'  => $request->description,
    //             'video_url'    => $request->video_url,
    //             'video_upload' => $request->video_upload,
    //             'image_upload' => $request->image_upload,
    //         ]);

    //         return $this->successResponse($portfolio, 'Portfolio created successfully', 201);
    //     } catch (\Exception $e) {
    //         return $this->errorResponse('Failed to create portfolio', 500, $e->getMessage());
    //     }
    // }


    // public function update(Request $request, $id)
    // {
    //     try {
    //         $portfolio = UserPortfolio::where('user_id', auth()->id())->find($id);

    //         if (!$portfolio) {
    //             return $this->errorResponse('Portfolio not found', 404);
    //         }

    //         $request->validate([
    //             'title'        => [
    //                 'required',
    //                 'string',
    //                 'max:255',
    //                 Rule::unique('user_portfolios')->where(function ($query) {
    //                     return $query->where('user_id', auth()->id());
    //                 })->ignore($portfolio->id)
    //             ],
    //             'description'  => 'nullable|string|max:1000',
    //             'video_url'    => 'nullable|url|regex:/^(https?:\/\/)/',
    //             'video_upload' => 'nullable|string|regex:/\.(mp4|mov|avi)$/i',
    //             'image_upload' => 'nullable|string|regex:/\.(jpg|jpeg|png|gif)$/i',
    //         ]);

    //         // Condition: At least one media required
    //         if (!$request->video_url && !$request->video_upload && !$request->image_upload) {
    //             return $this->errorResponse('At least one media (video or image) is required', 422);
    //         }

    //         $portfolio->update($request->only('title','description','video_url','video_upload','image_upload'));

    //         return $this->successResponse($portfolio, 'Portfolio updated successfully');
    //     } catch (\Exception $e) {
    //         return $this->errorResponse('Failed to update portfolio', 500, $e->getMessage());
    //     }
    // }

  