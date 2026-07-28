import SwiftUI
import WebKit

struct ContentView: View {
    @State private var isLoading = true
    @State private var showOfflineAlert = false
    @AppStorage("backend_url") private var backendURL = "http://127.0.0.1:8000"
    @AppStorage("frontend_url") private var frontendURL = "http://127.0.0.1:3000"

    var body: some View {
        ZStack {
            WebView(
                url: frontendURL,
                backendURL: backendURL,
                didFinish: { isLoading = false }
            )
            .edgesIgnoringSafeArea(.all)

            if isLoading {
                SplashView()
            }
        }
        .onAppear {
            checkConnectivity()
        }
        .alert("No Internet Connection", isPresented: $showOfflineAlert) {
            Button("Retry") { checkConnectivity() }
            Button("Open Settings") {
                if let url = URL(string: UIApplication.openSettingsURLString) {
                    UIApplication.shared.open(url)
                }
            }
        } message: {
            Text("Connect to the internet to use Intan Elyu.")
        }
    }

    private func checkConnectivity() {
        guard let url = URL(string: frontendURL) else { return }
        var request = URLRequest(url: url)
        request.timeoutInterval = 5
        URLSession.shared.dataTask(with: request) { _, resp, err in
            DispatchQueue.main.async {
                if let httpResp = resp as? HTTPURLResponse, httpResp.statusCode == 200 {
                    showOfflineAlert = false
                } else {
                    showOfflineAlert = err != nil
                }
            }
        }.resume()
    }
}

struct SplashView: View {
    @State private var opacity = 0.0

    var body: some View {
        ZStack {
            Color(red: 0.055, green: 0.09, blue: 0.165)
                .edgesIgnoringSafeArea(.all)
            VStack(spacing: 16) {
                Image(systemName: "mountain.2.fill")
                    .font(.system(size: 60))
                    .foregroundColor(.white.opacity(0.6))
                Text("Intan Elyu")
                    .font(.title)
                    .fontWeight(.bold)
                    .foregroundColor(.white)
                Text("La Union Tourism Guide")
                    .font(.subheadline)
                    .foregroundColor(.white.opacity(0.5))
                ProgressView()
                    .tint(.white)
                    .padding(.top, 20)
            }
        }
        .opacity(opacity)
        .onAppear {
            withAnimation(.easeIn(duration: 0.6)) { opacity = 1.0 }
        }
    }
}

#Preview {
    ContentView()
}
