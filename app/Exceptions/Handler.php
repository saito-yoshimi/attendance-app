<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        // API パスのときだけ JSON を返す
        if ($request->is('api/*')) {

            // 404: モデルが見つからない
            if ($e instanceof ModelNotFoundException) {
                return response()->json([
                    'error' => '勤怠情報が見つかりませんでした。',
                ], 404);
            }

            // 403: 認可エラー
            if ($e instanceof AuthorizationException) {
                return response()->json([
                    'error' => 'この操作を実行する権限がありません。',
                ], 403);
            }
        }

        // それ以外は通常の Laravel の処理
        return parent::render($request, $e);
    }

    public function register(): void
    {
        $this->renderable(function (
            ModelNotFoundException $e,
            $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => '勤怠情報が見つかりませんでした。',
                ], 404);
            }
        });

        $this->renderable(function (
            AuthenticationException $e,
            $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }
        });

        $this->renderable(function (
            AuthorizationException $e,
            $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => 'この操作を実行する権限がありません。',
                ], 403);
            }
        });

        $this->renderable(function (
            ValidationException $e,
            $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => '入力内容に誤りがあります。',
                    'errors' => $e->errors(),
                ], 422);
            }
        });
    }
}
