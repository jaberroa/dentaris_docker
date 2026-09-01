import React from "react";
import { Link } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { Heart, ArrowLeft } from "lucide-react";
import { ThemeToggle } from "@/components/ui/theme-toggle";

interface PublicHeaderProps {
  showBackToHome?: boolean;
  showActions?: boolean;
  variant?: "default" | "auth";
}

const PublicHeader: React.FC<PublicHeaderProps> = ({ 
  showBackToHome = false, 
  showActions = true,
  variant = "default"
}) => {
  return (
    <nav className="border-b bg-background/95 backdrop-blur-md sticky top-0 z-50">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          {/* Logo */}
          <Link to="/" className="flex items-center space-x-2">
            <Heart className="h-8 w-8 text-primary" />
            <span className="font-bold text-xl text-foreground">ClinicPro</span>
          </Link>

          {/* Navigation Actions */}
          <div className="flex items-center space-x-4">
            {showBackToHome && (
              <Link to="/">
                <Button variant="outline" size="sm">
                  <ArrowLeft className="w-4 h-4 mr-2" />
                  Back to Home
                </Button>
              </Link>
            )}
            
            {/* Theme Toggle */}
            <ThemeToggle />
            
            {showActions && variant === "default" && (
              <>
                <Link to="/login">
                  <Button variant="outline" size="sm">
                    Login
                  </Button>
                </Link>
                <Link to="/register">
                  <Button size="sm">
                    Sign Up
                  </Button>
                </Link>
              </>
            )}
            
            {showActions && variant === "auth" && (
              <Link to="/login">
                <Button size="sm">Try Demo</Button>
              </Link>
            )}
          </div>
        </div>
      </div>
    </nav>
  );
};

export default PublicHeader;

